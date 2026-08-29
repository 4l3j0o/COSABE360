<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScore extends Model
{
    protected $table = 'grade_scores';

    protected $fillable = [
        'evaluacion_id',
        'assessment_id',
        'estudiante_id',
        'student_id',
        'nota',
        'promedio_ponderado',
        'observaciones',
        'registrado_por',
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

    protected static function booted(): void
    {
        static::saving(function (GradeScore $model): void {
            if ($model->evaluacion_id && ! $model->assessment_id) {
                $model->assessment_id = $model->evaluacion_id;
            }
            if ($model->assessment_id && ! $model->evaluacion_id) {
                $model->evaluacion_id = $model->assessment_id;
            }
            if ($model->estudiante_id && ! $model->student_id) {
                $model->student_id = $model->estudiante_id;
            }
            if ($model->student_id && ! $model->estudiante_id) {
                $model->estudiante_id = $model->student_id;
            }
        });
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'evaluacion_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'estudiante_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public static function calcularPromedioPonderado(Student $student, Assessment $assessment): float
    {
        $colEvaluacion = $assessment->course_subject_id ? 'assessment_id' : 'evaluacion_id';
        $colEstudiante = 'student_id';

        $scores = static::where($colEstudiante, $student->id)
            ->whereHas($colEvaluacion === 'assessment_id' ? 'assessment' : 'evaluacion', function ($q) use ($assessment) {
                if ($assessment->course_subject_id) {
                    $q->where('course_subject_id', $assessment->course_subject_id)
                        ->where('bimestre', $assessment->bimestre);
                } else {
                    $q->where('materia_id', $assessment->materia_id)
                        ->where('gestion_id', $assessment->gestion_id);
                }
            })
            ->with($colEvaluacion === 'assessment_id' ? 'assessment' : 'evaluacion')
            ->get();

        $rel = $colEvaluacion === 'assessment_id' ? 'assessment' : 'evaluacion';
        $sumPesos = $scores->sum($rel.'.peso');
        if ($sumPesos <= 0) {
            return 0;
        }

        return round($scores->sum(fn ($s) => $s->nota * ($s->{$rel}->peso / $sumPesos)), 2);
    }
}
