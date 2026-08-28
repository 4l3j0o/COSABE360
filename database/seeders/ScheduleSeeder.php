<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Genera el horario semanal de cada paralelo a partir de su asignación de materias.
     *
     * - Días de clase: LUNES a VIERNES con bloques de 45 min y recreo (según entrevista).
     * - Cada materia se reparte en la semana según sus horas_semanales, distribuidas
     *   entre distintos días (ej. Matemáticas 6h → Lun 2, Mar 2, Mié 2).
     * - Usa el docente ya asignado al paralelo (misma materia puede tener distintos
     *   docentes según el paralelo: docente A en paralelo A, docente B en paralelo B).
     * - Cada paralelo tiene un aula fija (su salón), por lo que nunca hay cruces de aula.
     * - Evita cruces de horario para el mismo docente (CA-28-02) y solapamientos
     *   dentro del paralelo.
     */
    public function run(): void
    {
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];

        // Bloques de 45 min con recreo de 10:15 a 10:30
        $bloques = [
            ['08:00', '08:45'],
            ['08:45', '09:30'],
            ['09:30', '10:15'],
            ['10:30', '11:15'],
            ['11:15', '12:00'],
        ];
        $totalBloques = count($bloques);
        $totalDias = count($dias);

        $cursoMaterias = CourseSubject::with(['subject', 'teacher'])
            ->whereHas('teacher')
            ->get()
            ->groupBy('course_id');

        // Aula fija de cada paralelo (course.aula)
        $aulasCurso = Course::pluck('aula', 'id');

        // Reserva global docente|dia|bloque para que un docente no esté en dos
        // paralelos a la misma hora (equivalente a CA-28-02).
        $ocupadoDocente = [];

        Schedule::query()->delete();

        foreach ($cursoMaterias as $courseId => $items) {
            $ocupadoCurso = []; // dia|bloque ya usados en este paralelo
            // El aula del horario coincide con el aula del paralelo (su salón fijo)
            $aula = $aulasCurso->get($courseId);
            if (blank($aula)) {
                $aula = 'Aula '.$courseId;
            }

            // Prioridad: materias con más horas semanales primero
            $items = $items->sortByDesc(fn ($cs) => (int) ($cs->subject->horas_semanales ?? 1))->values();

            $indiceMateria = 0;
            foreach ($items as $cs) {
                $subject = $cs->subject;
                $teacher = $cs->teacher;

                $capacidad = $totalDias * $totalBloques;
                $horas = max(1, min((int) ($subject->horas_semanales ?? 1), $capacidad));

                // Día inicial rotado por materia para repartir el arranque de cada una
                $diaInicio = $indiceMateria % $totalDias;
                $indiceMateria++;

                $asignadas = 0;
                $i = 0;
                while ($asignadas < $horas && $i < ($capacidad * 2)) {
                    // Reparte las horas de la materia entre días distintos
                    $dia = $dias[($diaInicio + intdiv($i * $totalDias, $horas)) % $totalDias];
                    $bloque = $bloques[$i % $totalBloques];
                    $indiceBloque = $i % $totalBloques;
                    $i++;

                    if (isset($ocupadoCurso[$dia.'|'.$indiceBloque])) {
                        continue;
                    }
                    if (isset($ocupadoDocente[$teacher->id.'|'.$dia.'|'.$indiceBloque])) {
                        continue;
                    }

                    $ocupadoCurso[$dia.'|'.$indiceBloque] = true;
                    $ocupadoDocente[$teacher->id.'|'.$dia.'|'.$indiceBloque] = true;

                    Schedule::updateOrCreate(
                        [
                            'course_id' => $courseId,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'dia' => $dia,
                            'hora_inicio' => $bloque[0],
                        ],
                        [
                            'hora_fin' => $bloque[1],
                            'aula' => $aula,
                        ]
                    );

                    $asignadas++;
                }
            }
        }

        $this->command->info('Horarios semanales generados para los paralelos.');
    }
}
