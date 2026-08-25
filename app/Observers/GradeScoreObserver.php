<?php

namespace App\Observers;

use App\Models\GradeScore;

class GradeScoreObserver
{
    public function created(GradeScore $gradeScore): void
    {
        $this->recalcularPromedio($gradeScore);
    }

    public function updated(GradeScore $gradeScore): void
    {
        $this->recalcularPromedio($gradeScore);
    }

    public function deleted(GradeScore $gradeScore): void
    {
        $this->recalcularPromedio($gradeScore);
    }

    private function recalcularPromedio(GradeScore $gradeScore): void
    {
        $student = $gradeScore->student;
        $assessment = $gradeScore->assessment;

        if (!$student || !$assessment) return;

        $promedio = GradeScore::calcularPromedioPonderado($student, $assessment);

        GradeScore::where('student_id', $student->id)
            ->whereHas('assessment', function ($q) use ($assessment) {
                $q->where('course_subject_id', $assessment->course_subject_id)
                  ->where('bimestre', $assessment->bimestre);
            })
            ->update(['promedio_ponderado' => $promedio]);
    }
}
