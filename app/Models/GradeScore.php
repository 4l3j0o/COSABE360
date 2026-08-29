<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScore extends Model
{
    protected $fillable = ['assessment_id', 'student_id', 'nota', 'promedio_ponderado', 'observaciones', 'registrado_por'];

    protected $casts = ['nota' => 'decimal:2', 'promedio_ponderado' => 'decimal:2'];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public static function calcularPromedioPonderado(Student $student, Assessment $assessment): float
    {
        $scores = static::where('student_id', $student->id)
            ->whereHas('assessment', function ($q) use ($assessment) {
                $q->where('course_subject_id', $assessment->course_subject_id)
                  ->where('bimestre', $assessment->bimestre);
            })
            ->with('assessment')
            ->get();

        $sumPesos = $scores->sum('assessment.peso');
        if ($sumPesos <= 0) return 0;

        return round($scores->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2);
    }
}
