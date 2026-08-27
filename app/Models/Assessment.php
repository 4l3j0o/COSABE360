<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'course_subject_id',
        'materia_id',
        'gestion_id',
        'nombre',
        'tipo',
        'bimestre',
        'peso',
        'fecha',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'peso' => 'float',
            'fecha' => 'date',
            'bimestre' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'materia_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'gestion_id');
    }

    public function courseSubject(): BelongsTo
    {
        return $this->belongsTo(CourseSubject::class, 'course_subject_id');
    }

    public function gradeScores(): HasMany
    {
        return $this->hasMany(GradeScore::class, 'evaluacion_id');
    }

    public function gradeScoresAlt(): HasMany
    {
        return $this->hasMany(GradeScore::class, 'assessment_id');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
