<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Registration;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Corrige el paralelo de las matrículas 2026 usando la lista oficial
 * Docs/Inscripciones-2026.xlsx (hoja "Almnos", columnas B y C).
 *
 * El dump original de estudiantes conserva los section_id del sistema
 * anterior. En 1ro y 2do de Secundaria esos IDs no coinciden con el orden
 * en que EstructuraAcademicaSeeder crea los paralelos, por lo que 115
 * estudiantes quedaban cruzados entre S1C, S2A, S2B y S2C.
 *
 * Es idempotente: solo mueve matrículas cuyo paralelo no coincide.
 *
 * Uso:
 *   php artisan db:seed --class=RepararCursosXlsxSeeder --force
 */
class RepararCursosXlsxSeeder extends Seeder
{
    public function run(): void
    {
        $archivo = base_path('Docs/Inscripciones-2026.xlsx');
        if (! file_exists($archivo)) {
            $this->command->error('No se encontró Docs/Inscripciones-2026.xlsx.');

            return;
        }

        $gestion = AcademicYear::where('estado', 'ACTIVA')->orderBy('id')->first();
        if (! $gestion) {
            $this->command->error('No existe una gestión académica activa.');

            return;
        }

        $mapa = [
            'S5A-BQS' => 'S5A',
            'S5B-ASP' => 'S5B',
            'S5C-CE' => 'S5C',
            'S6A-BQS' => 'S6A',
            'S6B-ASP' => 'S6B',
            'S6C-CE' => 'S6C',
        ];

        $secciones = Section::where('academic_year_id', $gestion->id)
            ->get()
            ->keyBy('codigo');

        $hoja = IOFactory::load($archivo)->getSheetByName('Almnos');
        if (! $hoja) {
            $this->command->error('La hoja "Almnos" no existe en el Excel.');

            return;
        }

        $stats = [
            'filas' => 0,
            'sin_codigo' => 0,
            'sin_paralelo' => 0,
            'sin_estudiante' => 0,
            'sin_matricula' => 0,
            'sin_seccion' => 0,
            'sin_cambios' => 0,
            'actualizadas' => 0,
        ];

        DB::transaction(function () use ($hoja, $gestion, $mapa, $secciones, &$stats) {
            for ($r = 4; $r <= $hoja->getHighestDataRow(); $r++) {
                $codigo = trim((string) $hoja->getCell('C'.$r)->getValue());
                $paralelo = trim((string) $hoja->getCell('B'.$r)->getValue());

                if ($codigo === '' && $paralelo === '') {
                    continue;
                }

                $stats['filas']++;

                if ($codigo === '') {
                    $stats['sin_codigo']++;
                    continue;
                }

                if ($paralelo === '') {
                    $stats['sin_paralelo']++;
                    continue;
                }

                $codigoSeccion = $mapa[$paralelo] ?? $paralelo;
                $seccion = $secciones->get($codigoSeccion);

                if (! $seccion) {
                    $stats['sin_seccion']++;
                    $this->command->warn("Sin sección {$codigoSeccion} para el estudiante {$codigo}.");

                    continue;
                }

                $estudiante = Student::where('codigo', $codigo)->first();
                if (! $estudiante) {
                    $stats['sin_estudiante']++;
                    $this->command->warn("Sin estudiante para el código {$codigo}.");

                    continue;
                }

                $matricula = Enrollment::where('student_id', $estudiante->id)
                    ->where('academic_year_id', $gestion->id)
                    ->first();

                if (! $matricula) {
                    $stats['sin_matricula']++;
                    $this->command->warn("Sin inscripción {$gestion->nombre} para el estudiante {$codigo}.");

                    continue;
                }

                $registro = Registration::where('enrollment_id', $matricula->id)->first();
                if (! $registro) {
                    $stats['sin_matricula']++;
                    $this->command->warn("Sin matrícula para el estudiante {$codigo}.");

                    continue;
                }

                if ((int) $registro->section_id === (int) $seccion->id) {
                    $stats['sin_cambios']++;
                    continue;
                }

                $registro->update(['section_id' => $seccion->id]);
                $stats['actualizadas']++;
            }
        });

        $this->command->info(
            'Paralelos revisados: '.$stats['filas'].
            '. Actualizados: '.$stats['actualizadas'].
            '. Sin cambios: '.$stats['sin_cambios'].
            '. Sin estudiante: '.$stats['sin_estudiante'].
            '. Sin matrícula: '.$stats['sin_matricula'].
            '. Sin sección: '.$stats['sin_seccion'].'.'
        );
    }
}
