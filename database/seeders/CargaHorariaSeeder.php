<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * HU-70: carga la ocupación semanal de docentes desde
 * Docs/Horarios/Carga_Horaria_Docentes.xlsx (● = da clases).
 *
 * Por ahora SOLO marca ocupación (docente + día + hora): section_id y
 * subject_id quedan NULL hasta recibir los paralelos. Es idempotente:
 * borra los bloques de ocupación previos y los regenera.
 *
 * Periodos tomados de Docs/Horarios/HORARIO_PERIODOS.html.
 */
class CargaHorariaSeeder extends Seeder
{
    private function periodos(): array
    {
        return [
            'Nivel Inicial' => Schedule::PERIODOS_NIVEL['INICIAL'],
            'Nivel Primaria' => Schedule::PERIODOS_NIVEL['PRIMARIA'],
            'Nivel Secundaria' => Schedule::PERIODOS_NIVEL['SECUNDARIA'],
        ];
    }

    private const DIAS = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];

    public function run(): void
    {
        $archivo = database_path('../Docs/Horarios/Carga_Horaria_Docentes.xlsx');
        if (! file_exists($archivo)) {
            // Ruta cuando Docs vive fuera de database/
            $archivo = base_path('Docs/Horarios/Carga_Horaria_Docentes.xlsx');
        }
        if (! file_exists($archivo)) {
            $this->command->error('No se encontró Carga_Horaria_Docentes.xlsx en Docs/Horarios.');

            return;
        }

        Schedule::whereNull('section_id')->delete();

        $wb = IOFactory::load($archivo);
        $creados = 0;
        $omitidos = [];
        $traslapes = [];

        $sergioId = Teacher::where('apellidos', 'like', 'APARICIO%')->where('estado', 'ACTIVO')->value('id');

        foreach ($this->periodos() as $sheetName => $periodos) {
            $sh = $wb->getSheetByName($sheetName);
            if (! $sh) {
                $this->command->warn("Hoja '{$sheetName}' no encontrada, se omite.");
                continue;
            }

            $mapa = $this->mapaColumnas($sh, count($periodos));
            if (empty($mapa)) {
                $this->command->warn("Hoja '{$sheetName}': no se halló fila de periodos, se omite.");
                continue;
            }

            $maxRow = $sh->getHighestRow();
            for ($row = $mapa['desdeFila']; $row <= $maxRow; $row++) {
                $nombre = trim((string) $sh->getCell([1, $row])->getValue());
                if ($nombre === '' || preg_match('/^(docente|nivel|caso aparte)/i', $nombre)) {
                    continue;
                }

                $teacher = $this->resolverDocente($nombre);
                if (! $teacher) {
                    $omitidos[] = "{$sheetName} | {$nombre} (sin match en teachers)";
                    continue;
                }

                foreach ($mapa['cols'] as $col => $info) {
                    $v = trim((string) $sh->getCell([$col, $row])->getValue());
                    if ($v !== '●') {
                        continue; // 'zz' y demás se ignoran
                    }

                    [$ini, $fin] = $periodos[$info['periodo'] - 1];

                    // Caso Sergio Aparicio (Primaria, viernes sale 11:50)
                    if ($sergioId && $teacher->id === $sergioId && $sheetName === 'Nivel Primaria' && $info['dia'] === 'VIERNES') {
                        if ($ini >= '11:50') {
                            continue;
                        }
                        $fin = min($fin, '11:50');
                    }

                    $existe = Schedule::where('teacher_id', $teacher->id)
                        ->where('dia', $info['dia'])
                        ->where('hora_inicio', $ini . ':00')
                        ->where('hora_fin', $fin . ':00')
                        ->where('estado', 'ACTIVO')
                        ->exists();
                    if ($existe) {
                        $traslapes[] = "{$teacher->id} {$info['dia']} {$ini}-{$fin} (duplicado entre hojas)";
                        continue;
                    }

                    $cruce = Schedule::where('teacher_id', $teacher->id)
                        ->where('dia', $info['dia'])
                        ->where('estado', 'ACTIVO')
                        ->where('hora_inicio', '<', $fin . ':00')
                        ->where('hora_fin', '>', $ini . ':00')
                        ->exists();
                    if ($cruce) {
                        $traslapes[] = "{$nombre} {$info['dia']} {$ini}-{$fin} (traslape con otro bloque)";
                    }

                    Schedule::create([
                        'section_id' => null,
                        'subject_id' => null,
                        'teacher_id' => $teacher->id,
                        'dia' => $info['dia'],
                        'hora_inicio' => $ini,
                        'hora_fin' => $fin,
                        'estado' => 'ACTIVO',
                    ]);
                    $creados++;
                }
            }
        }

        $this->command->info("Carga horaria: {$creados} bloques de ocupación creados.");
        foreach (array_unique($omitidos) as $msg) {
            $this->command->warn('Omitido: ' . $msg);
        }
        foreach (array_unique($traslapes) as $msg) {
            $this->command->warn('Traslape: ' . $msg);
        }
    }

    /**
     * Devuelve [col => ['dia' => ..., 'periodo' => N]] y la primera fila de datos.
     * Fila de periodos = primera con etiquetas P1..Pn; días por forward-fill de la fila superior.
     */
    private function mapaColumnas($sh, int $nPeriodos): array
    {
        $maxCol = Coordinate::columnIndexFromString($sh->getHighestDataColumn());
        $maxRow = min($sh->getHighestRow(), 10);

        $filaPeriodos = null;
        for ($r = 1; $r <= $maxRow; $r++) {
            $pn = 0;
            for ($c = 2; $c <= $maxCol; $c++) {
                if (preg_match('/^P\d+$/i', trim((string) $sh->getCell([$c, $r])->getValue()))) {
                    $pn++;
                }
            }
            if ($pn >= 3) {
                $filaPeriodos = $r;
                break;
            }
        }
        if (! $filaPeriodos) {
            return [];
        }

        $cols = [];
        $diaActual = null;
        for ($c = 2; $c <= $maxCol; $c++) {
            $pLabel = trim((string) $sh->getCell([$c, $filaPeriodos])->getValue());
            $diaCell = trim((string) $sh->getCell([$c, $filaPeriodos - 1])->getValue());
            $diaNorm = $this->normalizarDia($diaCell);
            if ($diaNorm) {
                $diaActual = $diaNorm;
            }
            if (preg_match('/^P(\d+)$/i', $pLabel, $m) && $diaActual && (int) $m[1] >= 1 && (int) $m[1] <= $nPeriodos) {
                $cols[$c] = ['dia' => $diaActual, 'periodo' => (int) $m[1]];
            }
        }

        return ['cols' => $cols, 'desdeFila' => $filaPeriodos + 1];
    }

    private function normalizarDia(string $s): ?string
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
        foreach (self::DIAS as $d) {
            if ($s === $d || str_starts_with($s, $d)) {
                return $d;
            }
        }

        return null;
    }

    private function tokens(string $s): array
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);

        return array_values(array_unique(preg_split('/[^A-Z]+/', $s, -1, PREG_SPLIT_NO_EMPTY)));
    }

    private function resolverDocente(string $nombreExcel): ?Teacher
    {
        // Casos especiales confirmados
        $t = $this->tokens($nombreExcel);
        $key = implode(' ', $t);

        $especial = [
            'MENDOZA ACOSTA ERLAN LUIS ALBERTO' => ['nombres' => 'ERLAN%', 'apellidos' => 'MENDOZA ACOSTA'],
            'GIONNA CUEVAS' => ['nombres' => 'GIOVANNA%', 'apellidos' => 'CUEVAS%'],
            'FANNY ORTEGA' => ['nombres' => '%FANI%', 'apellidos' => '%ORTEGA%'],
            'ISLEN LOPEZ' => ['nombres' => 'ILSEN%', 'apellidos' => 'LOPEZ%'],
            'PROFE NUEVO' => ['nombres' => 'JONATHAN%', 'apellidos' => 'VIDEZ%'],
            'BERTHY RAFAEL VACA SALDANA' => ['nombres' => 'BERTHY%', 'apellidos' => 'VACA%'],
        ];
        if (isset($especial[$key])) {
            $e = $especial[$key];

            return Teacher::where('nombres', 'like', $e['nombres'])
                ->where('apellidos', 'like', $e['apellidos'])
                ->where('estado', 'ACTIVO')
                ->first();
        }

        // Match genérico: todos los tokens del Excel dentro del docente, único
        $candidatos = Teacher::where('estado', 'ACTIVO')->get()
            ->filter(fn ($doc) => empty(array_diff($t, $this->tokens($doc->nombres . ' ' . $doc->apellidos))));

        return $candidatos->count() === 1 ? $candidatos->first() : null;
    }
}
