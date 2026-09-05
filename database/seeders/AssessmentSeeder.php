<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\CourseSubject;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AssessmentSeeder extends Seeder
{
    /**
     * Genera 2 evaluaciones por bimestre (Parcial + Bimestral) para cada curso-materia
     * (course_subject), cubriendo los 4 bimestres de la gestión (bimestre 1 a 4).
     * Los pesos de cada bimestre suman 1.00 y las fechas caen en los días de clase
     * del paralelo (regla aplicada por AssessmentController::guardarCurso).
     */
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::first();

        if (! $gestion) {
            $this->command->error('No existe una gestión académica activa.');
            return;
        }

        $anio = (int) substr($gestion->fecha_inicio, 0, 4);

        // Por bimestre: una evaluación Parcial (40%) y una Bimestral (60%).
        $plantillas = [
            // Bimestre 1
            ['nombre' => 'Examen Parcial I',    'tipo' => 'Parcial',   'bimestre' => 1, 'peso' => 0.40, 'mes' => 2],
            ['nombre' => 'Examen Bimestral I',  'tipo' => 'Bimestral', 'bimestre' => 1, 'peso' => 0.60, 'mes' => 3],
            // Bimestre 2
            ['nombre' => 'Examen Parcial II',   'tipo' => 'Parcial',   'bimestre' => 2, 'peso' => 0.40, 'mes' => 5],
            ['nombre' => 'Examen Bimestral II', 'tipo' => 'Bimestral', 'bimestre' => 2, 'peso' => 0.60, 'mes' => 6],
            // Bimestre 3
            ['nombre' => 'Examen Parcial III',  'tipo' => 'Parcial',   'bimestre' => 3, 'peso' => 0.40, 'mes' => 8],
            ['nombre' => 'Examen Bimestral III','tipo' => 'Bimestral', 'bimestre' => 3, 'peso' => 0.60, 'mes' => 9],
            // Bimestre 4
            ['nombre' => 'Examen Parcial IV',   'tipo' => 'Parcial',   'bimestre' => 4, 'peso' => 0.40, 'mes' => 10],
            ['nombre' => 'Examen Bimestral IV', 'tipo' => 'Bimestral', 'bimestre' => 4, 'peso' => 0.60, 'mes' => 11],
        ];

        // Limpiar evaluaciones por curso generadas por este seeder (evita restos de
        // versiones previas al re-ejecutar). Las registradas manualmente se conservan.
        Assessment::whereNotNull('course_subject_id')
            ->where(function ($q) {
                $q->where('nombre', 'like', 'Examen Parcial%')
                    ->orWhere('nombre', 'like', 'Examen Bimestral%');
            })
            ->delete();

        $cursoMaterias = CourseSubject::with(['subject', 'course'])->get();

        foreach ($cursoMaterias as $cs) {
            $dias = Schedule::where('course_id', $cs->course_id)
                ->where('subject_id', $cs->subject_id)
                ->where('teacher_id', $cs->teacher_id)
                ->pluck('dia')
                ->unique()
                ->values();

            $descripcion = 'Evaluación de ' . $cs->subject->nombre . ' - ' . ($cs->course->nombre ?? '');

            foreach ($plantillas as $plantilla) {
                Assessment::updateOrCreate(
                    [
                        'course_subject_id' => $cs->id,
                        'nombre'            => $plantilla['nombre'],
                        'bimestre'          => $plantilla['bimestre'],
                    ],
                    [
                        'subject_id'       => $cs->subject_id,
                        'academic_year_id' => $gestion->id,
                        'tipo'             => $plantilla['tipo'],
                        'peso'             => $plantilla['peso'],
                        'fecha'            => $this->fechaPara($dias, $anio, $plantilla['mes']),
                        'descripcion'      => $descripcion,
                        'estado'           => 'ACTIVO',
                    ]
                );
            }
        }

        $this->command->info('Evaluaciones por curso generadas: Parcial y Bimestral en los 4 bimestres.');
    }

    private function fechaPara($dias, int $anio, int $mes): string
    {
        $nombres = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
        $posibles = $dias->isNotEmpty() ? $dias->all() : array_slice($nombres, 1, 5); // sin horario: cualquier día hábil

        $inicio = Carbon::createFromDate($anio, $mes, 1);
        for ($i = 0; $i < 28; $i++) {
            $fecha = $inicio->copy()->addDays($i);
            if (in_array($nombres[$fecha->dayOfWeek], $posibles)) {
                return $fecha->format('Y-m-d');
            }
        }

        return sprintf('%04d-%02d-15', $anio, $mes);
    }
}