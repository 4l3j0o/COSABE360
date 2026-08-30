<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Genera asistencia para cada paralelo en 3 días hábiles del segundo
     * bimestre, con una mezcla realista de estados (mayoría PRESENTE).
     */
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::first();

        if (! $gestion) {
            $this->command->error('No existe una gestión académica activa.');
            return;
        }

        $fechas = $this->fechasHabiles(Carbon::parse($gestion->fecha_inicio), 3);

        $courses = Course::where('estado', 'ACTIVO')->get();

        foreach ($courses as $course) {
            $registrante = CourseSubject::where('course_id', $course->id)
                ->whereNotNull('teacher_id')
                ->first()?->teacher?->user_id;

            foreach ($fechas as $fecha) {
                $students = $course->students()->orderBy('students.id')->get();

                foreach ($students as $student) {
                    $semilla = $course->id + $student->id + (int) substr($fecha, 8, 2);

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
                            'course_id'  => $course->id,
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

        $this->command->info('Asistencias generadas para los paralelos.');
    }

    private function fechasHabiles(Carbon $inicio, int $cantidad): array
    {
        $ancla = $inicio->copy()->addMonths(7); // aprox. inicio de septiembre
        $fechas = [];
        $d = $ancla->copy();

        while (count($fechas) < $cantidad && $d->diffInDays($ancla) < 60) {
            if ($d->dayOfWeekIso <= 5) {
                $fechas[] = $d->format('Y-m-d');
            }
            $d->addDay();
        }

        return $fechas;
    }
}