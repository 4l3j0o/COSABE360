<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\HorarioExcelParser as P;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Carga los horarios REALES 2026 desde los Excel de Docs/Horarios:
 *  - SECUNDARIA HORARIO COSABE 2026 FINAL.xlsx → hojas "ÚLTIMO HORARIO" (S1-S6,
 *    tarde) y "HORARIO GRAL " (mini-bloque S4-S6 por la mañana + sábado + Inicial).
 *  - HORARIOS_INICIAL-PRIMARIA.xlsx → hojas "HORARIO GRAL " (P1-P6) y
 *    "HORARIOS INICIAL Y PRIMARIA" (grillas por paralelo de Inicial).
 *
 * Reglas (confirmadas por el colegio):
 *  - "LENGUAJE" del tutor en Primaria → CL; "CLASES PROF. TUTORA" (Inicial) →
 *    bloque con paralelo pero sin materia.
 *  - El mini-bloque de secundaria (mañana) usa las horas de Primaria.
 *  - GONZALO (TEC S6) = RUIZ GAITE; VALDEZ = VIDEZ (typos).
 *  - Donde el Excel difiere de la asignación, gana el Excel: se corrige
 *    section_subjects (S4 FIS→ORTEGA, S4C MAT→DURAN, P4 LE→RIVERA).
 *
 * Reemplaza los bloques CLASE ACTIVOS (ocupación + detalle previos) por el
 * horario oficial; no toca las visitas (tipo VISITA).
 */
class HorarioReal2026Seeder extends Seeder
{
    private const DIR = 'Docs/Horarios/';

    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();
        if (! $gestion) {
            $this->command->error('Sin gestión activa.');

            return;
        }

        $niveles = Level::orderBy('orden')->get()->keyBy(fn ($l) => strtoupper($l->nombre));
        $idNivel = [
            'INICIAL' => $niveles->first(fn ($l) => str_contains(mb_strtoupper($l->nombre), 'INICIAL'))?->id,
            'PRIMARIA' => $niveles->first(fn ($l) => str_contains(mb_strtoupper($l->nombre), 'PRIMARIA'))?->id,
            'SECUNDARIA' => $niveles->first(fn ($l) => str_contains(mb_strtoupper($l->nombre), 'SECUNDARIA'))?->id,
        ];

        $planeadas = $this->leerEntradas();

        $stats = ['ok' => 0, 'sin_seccion' => [], 'sin_materia' => [], 'sin_docente' => [], 'sigla' => [], 'doc' => []];
        $filas = [];   // para insertar
        $mapa = [];    // (section,subject) => teacher  (gana Excel)

        foreach ($planeadas as $e) {
            $par = $e['par'];
            $section = Section::where('codigo', $par)->where('academic_year_id', $gestion->id)->first()
                ?? Section::where('codigo', $par)->first();
            if (! $section) {
                $stats['sin_seccion'][] = $par;
                continue;
            }
            $nivelNombre = mb_strtoupper($section->grade->level->nombre);
            $nivel = str_contains($nivelNombre, 'INICIAL') ? 'INICIAL'
                : (str_contains($nivelNombre, 'SECUNDARIA') ? 'SECUNDARIA' : 'PRIMARIA');

            [$matTxt, $docTxt] = P::partirCelda($e['txt']);
            $sigla = P::siglaDe($matTxt, $nivel);
            if ($sigla === null) {
                continue; // PD / OPCIÓN
            }
            if ($sigla === '?') {
                $stats['sigla'][P::norma($matTxt)] = ($stats['sigla'][P::norma($matTxt)] ?? 0) + 1;
                continue;
            }
            $subject = $sigla === '' ? null
                : Subject::where('level_id', $section->grade->level_id)->where('sigla', $sigla)->first();
            if ($sigla !== '' && ! $subject) {
                $stats['sin_materia'][] = "{$par} {$sigla}";
                continue;
            }

            $horas = P::horasDe($e['turno'], $e['p1'], $e['p2']);
            if (! $horas) {
                $stats['sin_materia'][] = "{$par} {$matTxt} p{$e['p1']}-{$e['p2']} sin horas ({$e['turno']})";
                continue;
            }

            // Docente
            $teacher = null;
            $ident = P::docenteDe($docTxt, $nivel);
            if ($ident) {
                $teacher = Teacher::where('nombres', $ident[0])->where('apellidos', $ident[1])->where('estado', 'ACTIVO')->first();
            }
            if (! $teacher && $docTxt !== '') {
                $stats['doc'][P::norma($docTxt)] = ($stats['doc'][P::norma($docTxt)] ?? 0) + 1;
            }
            if (! $teacher && $subject) {
                // fallback: único docente de esa materia en el paralelo
                $cands = SectionSubject::where('section_id', $section->id)->where('subject_id', $subject->id)->pluck('teacher_id')->unique();
                $teacher = $cands->count() === 1 ? Teacher::find($cands->first()) : null;
            }
            if (! $teacher && $subject && $docTxt === '') {
                $stats['sin_docente'][] = "{$par} {$sigla} {$e['dia']} p{$e['p1']}";
            }
            // Bloque de tutoría en Inicial (sin materia): el docente es el tutor del paralelo.
            if (! $teacher && ! $subject) {
                $teacher = $section->responsable;
            }
            if (! $teacher) {
                $stats['sin_docente'][] = "{$par} " . ($sigla ?: 'TUTOR') . " {$e['dia']} p{$e['p1']} (sin docente ni tutor)";
                continue;
            }

            if ($subject && $teacher) {
                $mapa[$section->id . '|' . $subject->id][$teacher->id] = true;
            }

            // Un bloque por periodo (las grillas muestran periodos sueltos).
            for ($p = $e['p1']; $p <= $e['p2']; $p++) {
                $h = P::horasDe($e['turno'], $p, $p);
                if (! $h) {
                    continue;
                }
                $filas[] = [
                    'section_id' => $section->id,
                    'subject_id' => $subject?->id,
                    'teacher_id' => $teacher?->id,
                    'tipo' => 'CLASE',
                    'dia' => $e['dia'],
                    'hora_inicio' => $h[0] . ':00',
                    'hora_fin' => $h[1] . ':00',
                    'aula' => $section->aula,
                    'estado' => 'ACTIVO',
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            $stats['ok']++;
        }

        // 1) Corregir asignaciones donde el Excel manda un docente único distinto
        $asignaciones = 0;
        foreach ($mapa as $clave => $set) {
            [$sectionId, $subjectId] = explode('|', $clave);
            if (count($set) !== 1) {
                continue; // varios docentes: no forzar
            }
            $teacherId = array_key_first($set);
            $existe = SectionSubject::where('section_id', $sectionId)->where('subject_id', $subjectId)->first();
            if (! $existe) {
                SectionSubject::create(['section_id' => $sectionId, 'subject_id' => $subjectId, 'teacher_id' => $teacherId]);
                $asignaciones++;

                continue;
            }
            if ((int) $existe->teacher_id !== (int) $teacherId) {
                // Si ya hay fila con ese docente, eliminar la duplicada; si no, actualizar.
                $yaExiste = SectionSubject::where('section_id', $sectionId)
                    ->where('subject_id', $subjectId)
                    ->where('teacher_id', $teacherId)
                    ->exists();
                if ($yaExiste) {
                    $existe->delete();
                } else {
                    $existe->update(['teacher_id' => $teacherId]);
                }
                $asignaciones++;
            }
        }

        // 2) Reemplazar bloques de clase
        DB::table('schedules')->where('tipo', 'CLASE')->where('estado', 'ACTIVO')->delete();
        foreach (array_chunk($filas, 200) as $chunk) {
            DB::table('schedules')->insert($chunk);
        }

        $this->command->info('Horario cargado: ' . count($filas) . " bloques (1 por periodo) desde {$stats['ok']} celdas del Excel; {$asignaciones} asignaciones corregidas.");
        foreach (['sin_seccion', 'sin_materia', 'sin_docente'] as $k) {
            if ($stats[$k]) {
                $this->command->warn("{$k} (" . count($stats[$k]) . '): ' . implode(' | ', array_slice(array_unique($stats[$k]), 0, 15)));
            }
        }
        if ($stats['sigla']) {
            $this->command->warn('Siglas no reconocidas: ' . json_encode($stats['sigla'], JSON_UNESCAPED_UNICODE));
        }
        if ($stats['doc']) {
            $this->command->warn('Docentes no mapeados: ' . json_encode($stats['doc'], JSON_UNESCAPED_UNICODE));
        }
    }

    /** Entradas normalizadas: [par, dia, p1, p2, turno, txt]. */
    private function leerEntradas(): array
    {
        $out = [];

        // --- SECUNDARIA ---
        $ss = IOFactory::load(base_path(self::DIR . 'SECUNDARIA HORARIO COSABE 2026 FINAL.xlsx'));
        $grupos6 = [['B', ['C', 'D', 'E']], ['F', ['G', 'H', 'I']], ['J', ['K', 'L', 'M']], ['N', ['O', 'P', 'Q']], ['R', ['S', 'T', 'U']], ['V', ['W', 'X', 'Y']]];
        $cab18 = ['C', 'D', 'E', 'G', 'H', 'I', 'K', 'L', 'M', 'O', 'P', 'Q', 'S', 'T', 'U', 'W', 'X', 'Y'];

        $ult = $ss->getSheetByName('ÚLTIMO HORARIO');
        foreach (P::parseGeneral($ult, $grupos6, '/^S\d[A-C]/', $cab18) as $e) {
            $out[] = ['par' => trim(preg_replace('/\s*-\s*[A-Z]+$/', '', $e['par'])), 'dia' => $e['dia'], 'p1' => $e['p1'], 'p2' => $e['p2'], 'turno' => 'SECUNDARIA', 'txt' => $e['txt']];
        }

        // Mini-bloque (mañana + sábado + Inicial) en "HORARIO GRAL "
        $gral = $ss->getSheetByName('HORARIO GRAL ');
        $miniGrupos = [['B', ['C', 'D', 'E']], ['F', ['G', 'H', 'I']], ['J', ['K', 'L', 'M']], ['N', ['O', 'P', 'Q', 'S']]];
        $miniCab = ['C', 'D', 'E', 'G', 'H', 'I', 'K', 'L', 'M', 'O', 'P', 'Q', 'S'];
        foreach ($this->parseMini($gral, $miniGrupos, $miniCab) as $e) {
            $out[] = ['par' => trim(preg_replace('/\s*-\s*[A-Z]+$/', '', $e['par'])), 'dia' => $e['dia'], 'p1' => $e['p1'], 'p2' => $e['p2'], 'turno' => 'PRIMARIA', 'txt' => $e['txt']];
        }
        $ss->disconnectWorksheets();
        unset($ss);

        // --- PRIMARIA + INICIAL ---
        $wb = IOFactory::load(base_path(self::DIR . 'HORARIOS_INICIAL-PRIMARIA.xlsx'));
        $gp = $wb->getSheetByName('HORARIO GRAL ');
        foreach (P::parseGeneral($gp, $grupos6, '/^P\d[A-C]/', $cab18) as $e) {
            $out[] = ['par' => rtrim($e['par'], ' -'), 'dia' => $e['dia'], 'p1' => $e['p1'], 'p2' => $e['p2'], 'turno' => 'PRIMARIA', 'txt' => $e['txt']];
        }

        $iy = $wb->getSheetByName('HORARIOS INICIAL Y PRIMARIA');
        foreach (P::parseParalelos($iy, [$this, 'bloquesInicial']) as $e) {
            $out[] = ['par' => $e['par'], 'dia' => $e['dia'], 'p1' => $e['p1'], 'p2' => $e['p2'], 'turno' => 'INICIAL', 'txt' => $e['txt']];
        }
        $wb->disconnectWorksheets();

        return $out;
    }

    /** Detecta grillas de Inicial por título (INICIAL) en la fila dada. */
    public function bloquesInicial($sheet, int $r): array
    {
        $out = [];
        $num = ['PRIMERO' => 1, 'SEGUNDO' => 2];
        foreach (['B' => ['B', 'C', 'D', 'E', 'F'], 'I' => ['I', 'J', 'K', 'L', 'M']] as $colT => $cols) {
            $titulo = P::celda($sheet, $colT, $r);
            if (! preg_match('/HORARIO\s+(PRIMERO|SEGUNDO)\s*"([AB])".*INICIAL/i', $titulo, $m)) {
                continue;
            }
            $dias = [];
            foreach ($cols as $c) {
                $dias[$c] = P::normalizarDia(P::celda($sheet, $c, $r + 1));
            }
            $out[] = ['par' => 'I' . $num[mb_strtoupper($m[1])] . mb_strtoupper($m[2]), 'filaDias' => $r + 1, 'dias' => $dias];
        }

        return $out;
    }

    /** Mini-bloque de secundaria: un solo cabecero y días por columna A. */
    private function parseMini($sheet, array $grupos, array $cabCols): array
    {
        // Localiza la fila cabecera (C = S4A...)
        $cabRow = null;
        for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
            if (preg_match('/^S4[A-C]/', P::celda($sheet, $cabCols[0], $r))) {
                $cabRow = $r;
                break;
            }
        }
        if (! $cabRow) {
            return [];
        }
        $cab = [];
        foreach ($cabCols as $c) {
            $cab[$c] = P::celda($sheet, $c, $cabRow);
        }

        $out = [];
        $dia = null;
        for ($r = $cabRow + 1; $r <= $sheet->getHighestRow(); $r++) {
            $a = P::celda($sheet, 'A', $r);
            if ($a !== '') {
                $dia = P::normalizarDia($a) ?? $dia;
            }
            if (! $dia) {
                continue;
            }
            if (preg_match('/^S4[A-C]/', P::celda($sheet, $cabCols[0], $r))) {
                break;
            }
            foreach ($grupos as [$perCol, $cols]) {
                if (! preg_match('/^(\d)/', P::celda($sheet, $perCol, $r), $m)) {
                    continue;
                }
                foreach ($cols as $c) {
                    $txt = P::celda($sheet, $c, $r);
                    if ($txt === '' || mb_strtoupper($txt) === 'PD') {
                        continue;
                    }
                    [$r1, $r2] = P::spanDe($sheet, $c, $r);
                    if ($r !== $r1) {
                        continue;
                    }
                    $p = (int) $m[1];
                    $p2 = preg_match('/^(\d)/', P::celda($sheet, $perCol, $r2), $m2) ? (int) $m2[1] : $p;
                    $out[] = ['dia' => $dia, 'p1' => $p, 'p2' => $p2, 'par' => $cab[$c] ?? '?', 'txt' => $txt];
                }
            }
        }

        return $out;
    }
}
