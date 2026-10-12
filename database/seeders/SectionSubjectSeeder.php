<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SectionSubjectSeeder extends Seeder
{
    /**
     * Asigna a cada curso las materias de su nivel, con su docente según especialidad.
     * Coherente con la plantilla curricular por nivel (Inicial/Primaria/Secundaria).
     */
    public function run(): void
    {
        $teachers = Teacher::where('estado', 'ACTIVO')->get();
        $sections = Section::where('estado', 'ACTIVO')->get();

        foreach ($sections as $section) {
            $grade = $section->grade ?? Grade::find($section->grade_id);
            if (! $grade) {
                continue;
            }

            $subjects = Subject::where('level_id', $grade->level_id)->get();

            // Limpiar asignaciones previas del curso para evitar duplicados al rotar docentes
            SectionSubject::where('section_id', $section->id)->delete();

            foreach ($subjects as $subject) {
                $teacher = $this->docenteParaMateria($subject->nombre, $teachers, $section->id);

                if (! $teacher) {
                    continue;
                }

                SectionSubject::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'section_id' => $section->id,
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        }

        $this->command->info('Materias asignadas a los cursos con sus docentes.');
    }

    private function docenteParaMateria(string $materia, $teachers, int $sectionId): ?Teacher
    {
        $mapaEspecialidad = [
            'Matemáticas' => 'Matemáticas',
            'Pensamiento Matemático' => 'Matemáticas',
            'Lenguaje' => 'Lenguaje y Literatura',
            'Lenguaje y Comunicación' => 'Lenguaje y Literatura',
            'Lenguaje y Literatura' => 'Lenguaje y Literatura',
            'Ciencias Naturales' => 'Ciencias Naturales',
            'Ciencias de la Vida' => 'Ciencias Naturales',
            'Ciencias Sociales' => 'Historia',
            'Historia' => 'Historia',
            'Física' => 'Física',
            'Química' => 'Química',
            'Biología' => 'Ciencias Naturales',
            'Inglés' => 'Inglés',
            'Educación Física' => 'Educación Física',
            'Filosofía' => 'Filosofía',
            'Psicología' => 'Filosofía',
            'Informática' => 'Informática',
            'Expresión Artística' => 'Artes Plásticas',
            'Música y Ritmo' => 'Artes Plásticas',
            'Artes Plásticas' => 'Artes Plásticas',
            'Religión o Ética' => 'Filosofía',
        ];

        $especialidad = $mapaEspecialidad[$materia] ?? null;
        $candidatos = $teachers->filter(fn ($t) => $t->estado === 'ACTIVO' &&
            str_contains($t->especialidad, $especialidad ?? 'xx'));

        if ($candidatos->isEmpty()) {
            $candidatos = $teachers->filter(fn ($t) => $t->estado === 'ACTIVO');
        }

        // Alternar entre los docentes de la especialidad según el curso:
        // docente A al paralelo A, docente B al paralelo B, etc. (misma materia,
        // distintos docentes según paralelo).
        $lista = $candidatos->values();

        return $lista->get($sectionId % $lista->count()) ?: $lista->first();
    }
}
