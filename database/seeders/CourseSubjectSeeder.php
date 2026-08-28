<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CourseSubjectSeeder extends Seeder
{
    /**
     * Asigna a cada curso las materias de su nivel, con su docente según especialidad.
     * Coherente con la plantilla curricular por nivel (Inicial/Primaria/Secundaria).
     */
    public function run(): void
    {
        $teachers = Teacher::where('estado', 'ACTIVO')->get();
        $courses  = Course::where('estado', 'ACTIVO')->get();

        foreach ($courses as $course) {
            $grade = $course->grade ?? Grade::find($course->grade_id);
            if (! $grade) {
                continue;
            }

            $subjects = Subject::where('level_id', $grade->level_id)->get();

            foreach ($subjects as $subject) {
                $teacher = $this->docenteParaMateria($subject->nombre, $teachers);

                if (! $teacher) {
                    continue;
                }

                CourseSubject::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'course_id'  => $course->id,
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        }

        $this->command->info('Materias asignadas a los cursos con sus docentes.');
    }

    private function docenteParaMateria(string $materia, $teachers): ?Teacher
    {
        $mapaEspecialidad = [
            'Matemáticas'            => 'Matemáticas',
            'Pensamiento Matemático' => 'Matemáticas',
            'Lenguaje'               => 'Lenguaje y Literatura',
            'Lenguaje y Comunicación' => 'Lenguaje y Literatura',
            'Lenguaje y Literatura'  => 'Lenguaje y Literatura',
            'Ciencias Naturales'     => 'Ciencias Naturales',
            'Ciencias de la Vida'    => 'Ciencias Naturales',
            'Ciencias Sociales'      => 'Historia',
            'Historia'               => 'Historia',
            'Física'                 => 'Física',
            'Química'                => 'Química',
            'Biología'               => 'Ciencias Naturales',
            'Inglés'                 => 'Inglés',
            'Educación Física'       => 'Educación Física',
            'Filosofía'              => 'Filosofía',
            'Psicología'             => 'Filosofía',
            'Informática'            => 'Informática',
            'Expresión Artística'    => 'Inglés',
            'Música y Ritmo'         => 'Inglés',
            'Artes Plásticas'        => 'Inglés',
            'Religión o Ética'       => 'Filosofía',
        ];

        $especialidad = $mapaEspecialidad[$materia] ?? null;

        $teacher = $teachers->first(fn ($t) => str_contains($t->especialidad, $especialidad ?? ''));

        return $teacher ?? $teachers->first();
    }
}
