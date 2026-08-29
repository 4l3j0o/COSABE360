<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScore extends Model
{
    protected $table = 'grade_scores';

    protected $fillable = [
        'assessment_id',
        'student_id',
        'nota',
        'promedio_ponderado',
        'observaciones',
        'registered_by_user_id',
        'registrado_en',
    ];

    protected function casts(): array
    {
        return [
            'nota' => 'decimal:2',
            'promedio_ponderado' => 'decimal:2',
            'registrado_en' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public static function calcularPromedioPonderado(Student $student, Assessment $assessment): float
    {
        $scores = static::where('student_id', $student->id)
            ->whereHas('assessment', function ($q) use ($assessment) {
                if ($assessment->course_subject_id) {
                    $q->where('course_subject_id', $assessment->course_subject_id)
                        ->where('bimestre', $assessment->bimestre);
                } else {
                    $q->where('subject_id', $assessment->subject_id)
                        ->where('academic_year_id', $assessment->academic_year_id);
                }
            })
            ->with('assessment')
            ->get();

        $sumPesos = $scores->sum('assessment.peso');
        if ($sumPesos <= 0) {
            return 0;
        }

        return round($scores->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2);
    }
}
