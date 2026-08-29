<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\GradeScore;
use Illuminate\Database\Seeder;

class GradeScoreSeeder extends Seeder
{
    /**
     * Registra notas (0-100) para cada estudiante matriculado en las evaluaciones
     * por curso. Deja una parte sin nota para un panorama más realista.
     */
    public function run(): void
    {
        $assessments = Assessment::with('courseSubject.course')
            ->whereNotNull('course_subject_id')
            ->get();

        $procesadas = 0;

        foreach ($assessments as $assessment) {
            $curso = $assessment->courseSubject?->course;
            if (! $curso) {
                continue;
            }

            $registrante = $assessment->courseSubject->teacher?->user_id;

            $students = $curso->students()->orderBy('students.id')->get();

            foreach ($students as $student) {
                if (($assessment->id + $student->id) % 7 === 0) {
                    continue;
                }

                $nota = 51 + (($assessment->id * 31 + $student->id * 17) % 45);

                GradeScore::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'student_id'    => $student->id,
                    ],
                    [
                        'nota'                 => $nota,
                        'observaciones'        => null,
                        'registered_by_user_id' => $registrante,
                        'registrado_en'        => now(),
                    ]
                );
                $procesadas++;
            }
        }

        $this->command->info("Notas registradas en evaluaciones por curso: {$procesadas}");
    }
}