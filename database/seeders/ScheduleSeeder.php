<?php

namespace Database\Seeders;

use App\Models\CourseSubject;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Genera el horario semanal de cada curso a partir de su asignación de materias.
     * Distribuye las clases en horarios fijos evitando solapamientos dentro de un curso.
     */
    public function run(): void
    {
        // Franjas horarias disponibles (LUN-VIE, 6 franjas de 50 min aprox.)
        $franjas = [
            ['LUNES',    '08:00', '08:50'],
            ['LUNES',    '09:00', '09:50'],
            ['MARTES',   '08:00', '08:50'],
            ['MARTES',   '09:00', '09:50'],
            ['MIERCOLES', '08:00', '08:50'],
            ['MIERCOLES', '09:00', '09:50'],
            ['JUEVES',   '08:00', '08:50'],
            ['JUEVES',   '09:00', '09:50'],
            ['VIERNES',  '08:00', '08:50'],
            ['VIERNES',  '09:00', '09:50'],
        ];

        $courseSubjects = CourseSubject::with(['course', 'subject', 'teacher'])->get();

        // Agrupo por curso para distribuir las franjas sin solape
        $porCurso = $courseSubjects->groupBy('course_id');

        foreach ($porCurso as $courseId => $items) {
            $course = $items->first()->course;

            foreach ($items->values() as $i => $cs) {
                $franja = $franjas[$i % count($franjas)];

                Schedule::updateOrCreate(
                    [
                        'course_id'   => $course->id,
                        'subject_id'  => $cs->subject_id,
                        'teacher_id'  => $cs->teacher_id,
                        'dia'         => $franja[0],
                        'hora_inicio' => $franja[1],
                    ],
                    [
                        'hora_fin' => $franja[2],
                        'aula'     => 'Aula ' . ($i + 1),
                    ]
                );
            }
        }

        $this->command->info('Horarios semanales generados para los cursos.');
    }
}
