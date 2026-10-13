<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Section;
use App\Models\SectionSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Genera asistencia para cada paralelo a lo largo de los 4 bimestres de la
     * gestión: 3 días hábiles por bimestre (uno por semana de muestreo), con una
     * mezcla realista de estados (mayoría PRESENTE). El rango de fechas de cada
     * bimestre sigue la misma regla que AttendanceController::rangoBimestre.
     */
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::first();

        if (! $gestion) {
            $this->command->error('No existe una gestión académica activa.');
            return;
        }

        $fechas = $this->fechasPorBimestre($gestion, 3);

        $sections = Section::where('estado', 'ACTIVO')->get();

        foreach ($sections as $section) {
            $registrante = SectionSubject::where('section_id', $section->id)
                ->whereNotNull('teacher_id')
                ->first()?->teacher?->user_id;

            foreach ($fechas as $fecha) {
                $students = $section->students()->orderBy('students.id')->get();

                foreach ($students as $student) {
                    $semilla = $section->id + $student->id + (int) substr($fecha, 8, 2) + (int) substr($fecha, 5, 2);

                    $estado = 'PRESENTE';
                    if ($semilla % 17 === 0) {
                        $estado = 'FALTA';
                    } elseif ($semilla % 13 === 0) {
                        $estado = 'ATRASO';
                    } elseif ($semilla % 11 === 0) {
                        $estado = 'PERMISO';
                    }

                    Attendance::updateOrCreate(
                        [
                            'section_id'  => $section->id,
                            'student_id' => $student->id,
                            'fecha'      => Carbon::parse($fecha),
                        ],
                        [
                            'estado'                => $estado,
                            'justificacion'         => $estado === 'FALTA' ? 'Falta registrada por el docente' : null,
                            'registered_by_user_id' => $registrante,
                        ]
                    );
                }
            }
        }

        $this->command->info('Asistencias generadas por bimestre para los paralelos.');
    }

    /**
     * Devuelve una lista de fechas hábiles (L-S? laborales Lun-Vie) repartidas en
     * los 4 bimestres de la gestión, tomando $porBimestre días por bimestre.
     */
    private function fechasPorBimestre(AcademicYear $gestion, int $porBimestre): array
    {
        $inicio = $gestion->fecha_inicio
            ? Carbon::parse($gestion->fecha_inicio)
            : Carbon::create(Carbon::now()->year, 2, 1);

        $fechas = [];

        for ($b = 1; $b <= 4; $b++) {
            $ini = $inicio->copy()->addMonths(($b - 1) * 3)->startOfMonth();
            $fin = $inicio->copy()->addMonths($b * 3)->startOfMonth()->subDay();

            $d = $ini->copy();
            $tomadas = 0;
            // Recorre el bimestre día a día y toma los primeros $porBimestre días
            // hábiles como días de registro de asistencia.
            while ($d->lte($fin) && $tomadas < $porBimestre) {
                if ($d->dayOfWeekIso <= 5) {
                    $fechas[] = $d->format('Y-m-d');
                    $tomadas++;
                    // Distancia los días de muestreo dentro del bimestre.
                    $d->addDays(9);
                    continue;
                }
                $d->addDay();
            }
        }

        return $fechas;
    }
}
