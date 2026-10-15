<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Repara tutores importados con columnas corridas (dirección en el nombre,
 * barrio en el documento) usando Docs/Inscripciones-2026.xlsx como fuente.
 *
 * - 1 solo vínculo + datos limpios en el Excel → corrige la fila.
 * - Fila compartida entre distintas familias (dirección fantasma) → elimina
 *   los vínculos (la fila queda huérfana, no es una persona).
 * - Resto → se reporta para corrección manual (filtro "Por revisar").
 *
 * Uso (una sola vez, entorno privado con PII):
 *   php artisan db:seed --class=RepararTutoresXlsxSeeder --force
 * NO está en DatabaseSeeder: es una reparación puntual, no parte del seed.
 */
class RepararTutoresXlsxSeeder extends Seeder
{
    public function run(): void
    {
        $archivo = base_path('Docs/Inscripciones-2026.xlsx');
        if (! file_exists($archivo)) {
            $this->command->error('No se encontró Docs/Inscripciones-2026.xlsx.');

            return;
        }

        $xlsx = $this->cargarXlsx($archivo);
        $garbled = $this->filasCorridas();

        $stats = ['reparadas' => 0, 'fusionadas' => 0, 'desvinculadas' => 0, 'vinculos_borrados' => 0, 'manual' => 0, 'sin_tutor' => []];
        $manual = [];

        foreach ($garbled as $pg) {
            $vinc = DB::table('student_parent')->where('parent_guardian_id', $pg->id)->get();

            if ($vinc->count() === 1) {
                $resultado = $this->repararUnica($pg, $vinc->first(), $xlsx);
                if ($resultado === 'reparada' || $resultado === 'fusionada') {
                    $stats[$resultado === 'reparada' ? 'reparadas' : 'fusionadas']++;
                } else {
                    $stats['manual']++;
                    $manual[] = "pg={$pg->id} vínculo único sin datos [{$pg->nombres}|{$pg->apellidos}|{$pg->numero_documento}]";
                }
                continue;
            }

            if ($vinc->isEmpty()) {
                $stats['manual']++;
                continue;
            }

            if ($this->familiasDistintas($vinc, $xlsx)) {
                foreach ($vinc as $v) {
                    if ($v->es_principal) {
                        $est = Student::find($v->student_id);
                        $stats['sin_tutor'][] = $est ? $est->codigo : "student#{$v->student_id}";
                    }
                    DB::table('student_parent')
                        ->where('parent_guardian_id', $pg->id)
                        ->where('student_id', $v->student_id)
                        ->delete();
                    $stats['vinculos_borrados']++;
                }
                $stats['desvinculadas']++;
                continue;
            }

            // Posibles hermanos: fusionar vínculo por vínculo con la persona real.
            $pendientes = 0;
            foreach ($vinc as $v) {
                if ($this->fusionarVinculo($pg, $v, $xlsx)) {
                    $stats['fusionadas']++;
                } else {
                    $pendientes++;
                }
            }
            if (! DB::table('student_parent')->where('parent_guardian_id', $pg->id)->exists()) {
                $this->command->warn("Fila huérfana (se conserva para auditoría): pg={$pg->id} [{$pg->nombres}|{$pg->apellidos}|{$pg->numero_documento}]");
            }
            if ($pendientes > 0) {
                $stats['manual']++;
                $manual[] = "pg={$pg->id} {$pendientes} vínculo(s) sin persona real [{$pg->nombres}|{$pg->apellidos}|{$pg->numero_documento}]";
            }
        }

        $this->command->info("Tutores reparados: {$stats['reparadas']}. Vínculos fusionados a la fila real: {$stats['fusionadas']}. Filas fantasma desvinculadas: {$stats['desvinculadas']} ({$stats['vinculos_borrados']} vínculos). Para revisión manual: {$stats['manual']}.");
        foreach (array_slice($manual, 0, 20) as $m) {
            $this->command->warn($m);
        }
        if ($manual && count($manual) > 20) {
            $this->command->warn('... y ' . (count($manual) - 20) . ' más (ver filtro Por revisar en Tutores).');
        }
        if ($stats['sin_tutor']) {
            $this->command->warn('Sin tutor principal tras desvincular (' . count($stats['sin_tutor']) . '): ' . implode(', ', array_slice(array_unique($stats['sin_tutor']), 0, 30)));
        }

        $this->corregirRoles($xlsx);
        $this->desvincularBasura($xlsx);
        $this->vincularFaltantes($xlsx);
    }

    private function cargarXlsx(string $archivo): array
    {
        $sh = IOFactory::load($archivo)->getSheet(0);
        $xlsx = [];
        for ($r = 4; $r <= $sh->getHighestRow(); $r++) {
            $cod = $this->celda($sh, $r, 3);
            if ($cod === '' || $cod === '-') {
                continue;
            }
            $xlsx[$cod] = [
                'padre' => ['ci' => $this->celda($sh, $r, 12, true), 'nom' => $this->celda($sh, $r, 13), 'tel' => $this->celda($sh, $r, 14, true), 'email' => $this->celda($sh, $r, 15)],
                'madre' => ['ci' => $this->celda($sh, $r, 16, true), 'nom' => $this->celda($sh, $r, 17), 'tel' => $this->celda($sh, $r, 18, true), 'email' => $this->celda($sh, $r, 19)],
                'tutor' => ['ci' => $this->celda($sh, $r, 20, true), 'nom' => $this->celda($sh, $r, 21), 'tel' => $this->celda($sh, $r, 22, true), 'email' => $this->celda($sh, $r, 23)],
            ];
        }

        return $xlsx;
    }

    private function celda($sh, int $r, int $c, bool $fmt = false): string
    {
        $cell = $sh->getCell([$c, $r]);
        $v = $fmt ? $cell->getFormattedValue() : $cell->getValue();

        return trim((string) $v);
    }

    private function filasCorridas()
    {
        return DB::table('parent_guardians')
            ->where(fn ($q) => $q->whereRaw("numero_documento NOT REGEXP '[0-9]{5}'")->orWhereNull('numero_documento')
                ->orWhereRaw("TRIM(CONCAT(IFNULL(nombres,''), IFNULL(apellidos,''))) = ''"))
            ->get();
    }

    /**
     * Repara una fila de vínculo único con datos limpios del Excel.
     * Retorna 'reparada' (corrigió la fila), 'fusionada' (la persona ya
     * existía: movió el vínculo y borró la basura) o false (a manual).
     */
    private function repararUnica($pg, $vinc, array $xlsx)
    {
        $dat = $this->datosReales($pg, $vinc, $xlsx);
        if (! $dat) {
            return false;
        }
        if ($dat['otroId']) {
            return $this->fusionarVinculo($pg, $vinc, $xlsx) ? 'fusionada' : false;
        }

        $d = $dat['d'];
        $x = $dat['x'];
        $cambios = [
            'nombres' => $d[0], 'apellidos' => $d[1],
            'apellido_paterno' => $d[2], 'apellido_materno' => $d[3],
            'numero_documento' => $x['ci'], 'complemento' => '',
            'updated_at' => now(),
        ];
        if (empty($pg->telefono) && $x['tel'] !== '' && $x['tel'] !== '-') {
            $cambios['telefono'] = $x['tel'];
        }
        if (empty($pg->email) && filter_var($x['email'], FILTER_VALIDATE_EMAIL)) {
            $cambios['email'] = strtolower($x['email']);
        }
        DB::table('parent_guardians')->where('id', $pg->id)->update($cambios);

        return 'reparada';
    }

    /** Datos reales del Excel para un vínculo (o null si no hay). */
    private function datosReales($pg, $v, array $xlsx): ?array
    {
        $est = Student::find($v->student_id);
        $row = $est ? ($xlsx[trim((string) $est->codigo)] ?? null) : null;
        if (! $row) {
            return null;
        }
        $rol = $v->es_principal ? 'tutor' : strtolower((string) $v->tipo_relacion);
        if (! in_array($rol, ['padre', 'madre', 'tutor'], true)) {
            return null;
        }
        $x = $row[$rol];
        if (! $this->ciValido($x['ci'])) {
            return null;
        }
        $d = $this->dividir($x['nom']);
        if (! $this->limpio($d)) {
            return null;
        }
        $otroId = $this->personaPorCi($x['ci'], $pg->id);

        return ['x' => $x, 'd' => $d, 'otroId' => $otroId];
    }

    /**
     * Fila real de una persona por CI: match exacto, o por dígitos si es
     * único (cubre 'E-11493121' vs '11493121'). Null si hay ambigüedad.
     */
    private function personaPorCi(string $ci, ?int $excluirId = null): ?int
    {
        $q = DB::table('parent_guardians');
        if ($excluirId) {
            $q->where('id', '!=', $excluirId);
        }
        $exacto = (clone $q)->where('numero_documento', $ci)->value('id');
        if ($exacto) {
            return (int) $exacto;
        }
        $digitos = (string) preg_replace('/\D/', '', $ci);
        if ($digitos === '') {
            return null;
        }
        $candidatos = (clone $q)->pluck('id', 'numero_documento')
            ->filter(fn ($id, $doc) => (string) preg_replace('/\D/', '', (string) $doc) === $digitos)
            ->values();
        if ($candidatos->count() !== 1) {
            return null;
        }

        return (int) $candidatos->first();
    }

    /**
     * Mueve un vínculo a la fila real de la persona (deduplicando y
     * heredando principal). Retorna false si no hay persona real.
     */
    private function fusionarVinculo($pg, $v, array $xlsx): bool
    {
        $dat = $this->datosReales($pg, $v, $xlsx);
        if (! $dat || ! $dat['otroId']) {
            return false;
        }
        $otroId = $dat['otroId'];
        $ya = DB::table('student_parent')->where('parent_guardian_id', $otroId)->where('student_id', $v->student_id)->first();
        if ($ya) {
            // Duplicado: la persona ya está vinculada; hereda principal y se borra el vínculo basura.
            if ($v->es_principal && ! $ya->es_principal) {
                DB::table('student_parent')->where('parent_guardian_id', $otroId)->where('student_id', $v->student_id)->update(['es_principal' => true]);
            }
            DB::table('student_parent')->where('parent_guardian_id', $pg->id)->where('student_id', $v->student_id)->delete();
        } else {
            DB::table('student_parent')->where('parent_guardian_id', $pg->id)->where('student_id', $v->student_id)->update(['parent_guardian_id' => $otroId]);
        }
        // La fila basura huérfana se conserva para auditoría (no se borra).

        return true;
    }

    /** True si los vinculados tienen distinto padre (fila fantasma compartida). */
    private function familiasDistintas($vinc, array $xlsx): bool
    {
        $cis = [];
        foreach ($vinc as $v) {
            $est = Student::find($v->student_id);
            $row = $est ? ($xlsx[trim((string) $est->codigo)] ?? null) : null;
            if ($row && $row['padre']['ci'] !== '') {
                $cis[] = $row['padre']['ci'];
            }
        }

        return count(array_unique($cis)) > 1;
    }

    /**
     * Fase 2: roles cruzados. El Excel manda por CI (PADRE/MADRE/TUTOR);
     * si hay tutor en el Excel, él queda como principal.
     */
    private function corregirRoles(array $xlsx): void
    {
        $roles = 0;
        $principales = 0;

        foreach (Student::all(['id', 'codigo']) as $est) {
            $row = $xlsx[trim((string) $est->codigo)] ?? null;
            if (! $row) {
                continue;
            }
            $colDe = [];
            foreach (['padre' => 'PADRE', 'madre' => 'MADRE', 'tutor' => 'TUTOR'] as $k => $rol) {
                $digitos = $this->digitos($row[$k]['ci']);
                if ($this->ciValido($row[$k]['ci'])) {
                    $colDe[$digitos] = $rol;
                }
            }
            if (! $colDe) {
                continue;
            }

            $vincs = DB::table('student_parent')->where('student_id', $est->id)->get();
            foreach ($vincs as $v) {
                $pg = DB::table('parent_guardians')->where('id', $v->parent_guardian_id)->first();
                if (! $pg) {
                    continue;
                }
                $doc = (string) preg_replace('/\D/', '', (string) $pg->numero_documento);
                if (! isset($colDe[$doc]) || $colDe[$doc] === $v->tipo_relacion) {
                    continue;
                }
                DB::table('student_parent')
                    ->where('parent_guardian_id', $v->parent_guardian_id)
                    ->where('student_id', $est->id)
                    ->update(['tipo_relacion' => $colDe[$doc]]);
                $roles++;
            }

            // Principal = tutor del Excel (solo si el Excel trae tutor).
            $tutorDigitos = $this->digitos($row['tutor']['ci']);
            if ($this->ciValido($row['tutor']['ci'])) {
                foreach ($vincs as $v) {
                    $pg = DB::table('parent_guardians')->where('id', $v->parent_guardian_id)->first();
                    $esTutor = $pg && (string) preg_replace('/\D/', '', (string) $pg->numero_documento) === $tutorDigitos;
                    if ((bool) $v->es_principal !== $esTutor) {
                        DB::table('student_parent')
                            ->where('parent_guardian_id', $v->parent_guardian_id)
                            ->where('student_id', $est->id)
                            ->update(['es_principal' => $esTutor]);
                        $principales++;
                    }
                }
            }
        }

        $this->command->info("Roles corregidos: {$roles}. Principal ajustado al tutor: {$principales}.");
    }

    /**
     * Fase 3: desvincula filas que ni siquiera parecen una persona
     * (dirección en el nombre o "NOMBRE/SIN" y sin CI válido). Sus vínculos
     * son falsos. Las filas con nombre real pero sin CI van a manual.
     */
    private function desvincularBasura(array $xlsx): void
    {
        $placeholders = ['NOMBRE', 'SIN', 'S N', 'SN', 'N', 'X', 'XX', '-', '.', 'S/N', 'PASAJE SIN', 'CALLE SIN', 'AV SIN', 'SIN NOMBRE'];

        $vincBorrados = 0;
        $filas = 0;
        $sinTutor = [];

        foreach ($this->filasCorridas() as $pg) {
            $textoNombres = trim(($pg->nombres ?? '') . ' ' . ($pg->apellidos ?? ''));
            $norma = $this->norma($textoNombres);
            $tieneCi = (bool) preg_match('/\d{5,}/', (string) $pg->numero_documento);
            if ($textoNombres === '' || $tieneCi) {
                continue; // vacío (huérfana) o con CI: no tocar aquí
            }
            $partes = [$this->norma((string) ($pg->nombres ?? '')), $this->norma((string) ($pg->apellidos ?? ''))];
            $esPlaceholder = in_array($norma, $placeholders, true) || in_array($partes[0], $placeholders, true) || in_array($partes[1], $placeholders, true);
            if (preg_match('/^[\p{L}\s.\'-]+$/u', $textoNombres) && ! $esPlaceholder) {
                continue; // parece persona real sin CI → manual
            }

            $vincs = DB::table('student_parent')->where('parent_guardian_id', $pg->id)->get();
            foreach ($vincs as $v) {
                if ($v->es_principal) {
                    $est = Student::find($v->student_id);
                    $sinTutor[] = $est ? $est->codigo : "student#{$v->student_id}";
                }
                DB::table('student_parent')->where('parent_guardian_id', $pg->id)->where('student_id', $v->student_id)->delete();
                $vincBorrados++;
            }
            if ($vincs->isNotEmpty()) {
                $filas++;
            }
        }

        $this->command->info("Basura desvinculada: {$filas} filas ({$vincBorrados} vínculos).");
        if ($sinTutor) {
            $this->command->warn('Sin tutor principal tras desvincular (' . count($sinTutor) . '): ' . implode(', ', array_slice(array_unique($sinTutor), 0, 30)));
        }
    }

    /**
     * Fase 4: vincula personas reales del Excel que no tienen vínculo.
     * Si la persona ni siquiera tiene fila, la crea desde el Excel
     * (CI + nombre válido). No toca nada ambiguo.
     */
    private function vincularFaltantes(array $xlsx): void
    {
        $creados = 0;
        $personasCreadas = 0;
        $reporte = [];

        foreach (Student::all(['id', 'codigo']) as $est) {
            $row = $xlsx[trim((string) $est->codigo)] ?? null;
            if (! $row) {
                continue;
            }
            $rolesBd = DB::table('student_parent')->where('student_id', $est->id)->pluck('tipo_relacion')->all();
            $tienePrincipal = DB::table('student_parent')->where('student_id', $est->id)->where('es_principal', true)->exists();

            foreach (['padre' => 'PADRE', 'madre' => 'MADRE', 'tutor' => 'TUTOR'] as $k => $rol) {
                if (in_array($rol, $rolesBd, true) || ! $this->ciValido($row[$k]['ci'])) {
                    continue;
                }
                $d = $this->dividir($row[$k]['nom']);
                if (! $this->limpio($d)) {
                    $reporte[] = "est={$est->codigo} {$rol} nombre no válido: {$row[$k]['nom']}";
                    continue;
                }
                $personaId = $this->personaPorCi($row[$k]['ci']);
                if (! $personaId) {
                    // Cero filas con ese CI: crear la persona desde el Excel.
                    $candidatas = DB::table('parent_guardians')
                        ->pluck('id', 'numero_documento')
                        ->filter(fn ($id, $doc) => (string) preg_replace('/\D/', '', (string) $doc) === (string) preg_replace('/\D/', '', $row[$k]['ci']));
                    if (! $candidatas->isEmpty()) {
                        $reporte[] = "est={$est->codigo} {$rol} CI={$row[$k]['ci']} con dígitos duplicados, va a manual";
                        continue;
                    }
                    $personaId = DB::table('parent_guardians')->insertGetId([
                        'nombres' => $d[0], 'apellidos' => $d[1],
                        'apellido_paterno' => $d[2], 'apellido_materno' => $d[3],
                        'tipo_documento' => 'CI', 'numero_documento' => $row[$k]['ci'], 'complemento' => '',
                        'telefono' => $row[$k]['tel'] !== '-' ? $row[$k]['tel'] : null,
                        'email' => filter_var($row[$k]['email'], FILTER_VALIDATE_EMAIL) ? strtolower($row[$k]['email']) : null,
                        'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $personasCreadas++;
                }
                if (DB::table('student_parent')->where('parent_guardian_id', $personaId)->where('student_id', $est->id)->exists()) {
                    continue;
                }
                $principal = $rol === 'TUTOR' ? ! $tienePrincipal : ! $tienePrincipal && $rol === 'PADRE';
                DB::table('student_parent')->insert([
                    'student_id' => $est->id, 'parent_guardian_id' => $personaId,
                    'tipo_relacion' => $rol, 'es_principal' => $principal, 'convive' => true,
                ]);
                if ($principal) {
                    $tienePrincipal = true;
                }
                $creados++;
            }
        }

        $this->command->info("Vínculos creados a personas reales: {$creados} ({$personasCreadas} personas creadas).");
        foreach (array_slice($reporte, 0, 20) as $m) {
            $this->command->warn($m);
        }
        if (count($reporte) > 20) {
            $this->command->warn('... y ' . (count($reporte) - 20) . ' más.');
        }
    }

    /** Dígitos de un CI (ignora prefijos como "E-" y espacios). */
    private function digitos(string $ci): string
    {
        return (string) preg_replace('/\D/', '', $ci);
    }

    /** True si el CI trae al menos 5 dígitos. */
    private function ciValido(string $ci): bool
    {
        return (bool) preg_match('/^\d{5,}$/', $this->digitos($ci));
    }

    private function norma(string $s): string
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
        $s = preg_replace('/[^A-Z0-9 ]/', ' ', $s);

        return (string) preg_replace('/\s+/', ' ', trim($s));
    }

    /** "FLORES QUISPE RICHARD" → [nombres, apellidos, paterno, materno]. */
    private function dividir(string $completo): ?array
    {
        $t = preg_split('/\s+/', trim($completo));
        // Suelta puntuación suelta (".") y dígitos sueltos ("0" de "MARTINEZ 0 ...").
        $t = array_values(array_filter($t, fn ($x) => preg_match('/\p{L}/u', $x) || (preg_match('/\d/', $x) && mb_strlen($x) > 1)));
        if (count($t) >= 3) {
            return [implode(' ', array_slice($t, 2)), implode(' ', array_slice($t, 0, 2)), $t[0], $t[1]];
        }
        if (count($t) === 2) {
            return [$t[1], $t[0], $t[0], ''];
        }
        if (count($t) === 1) {
            return [$t[0], '', '', ''];
        }

        return null;
    }

    private function limpio(?array $d): bool
    {
        if (! $d || trim($d[0] . $d[1]) === '') {
            return false;
        }
        foreach ([$d[0], $d[1]] as $parte) {
            if ($parte !== '' && ! preg_match('/^[\p{L}\s.\'-]+$/u', $parte)) {
                return false;
            }
        }

        return true;
    }
}
