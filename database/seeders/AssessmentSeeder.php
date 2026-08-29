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
     * Genera 4 evaluaciones por cada curso-materia (course_subject) con pesos que
     * suman 1 por bimestre y fechas que caen en los días de clase del paralelo
     * (regla aplicada por AssessmentController::guardarCurso).
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

        $plantillas = [
            ['nombre' => 'Examen Parcial',      'tipo' => 'Examen',           'bimestre' => 1, 'peso' => 0.20, 'mes' => 4],
            ['nombre' => 'Examen Bimestral',    'tipo' => 'Examen',           'bimestre' => 1, 'peso' => 0.30, 'mes' => 5],
            ['nombre' => 'Trabajo Práctico',    'tipo' => 'Trabajo Práctico', 'bimestre' => 2, 'peso' => 0.15, 'mes' => 8],
            ['nombre' => 'Examen Bimestral II', 'tipo' => 'Examen',           'bimestre' => 2, 'peso' => 0.35, 'mes' => 9],
        ];

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
                    ],
                    [
                        'subject_id'       => $cs->subject_id,
                        'academic_year_id' => $gestion->id,
                        'tipo'             => $plantilla['tipo'],
                        'bimestre'         => $plantilla['bimestre'],
                        'peso'             => $plantilla['peso'],
                        'fecha'            => $this->fechaPara($dias, $anio, $plantilla['mes']),
                        'descripcion'      => $descripcion,
                        'estado'           => 'ACTIVO',
                    ]
                );
            }
        }

        $this->command->info('Evaluaciones por curso generadas con fechas en días de clase.');
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