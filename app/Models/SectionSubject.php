<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * HU-69: asignación materia ↔ paralelo ↔ docente (antes SectionSubject).
 */
class SectionSubject extends Model
{
    protected $table = 'section_subjects';

    protected $fillable = ['section_id', 'subject_id', 'teacher_id', 'horas_semanales'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'section_subject_id');
    }

    public function evaluationPlans(): HasMany
    {
        return $this->hasMany(EvaluationPlan::class, 'section_subject_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'subject_id', 'subject_id')
            ->where('section_id', $this->section_id)
            ->where('teacher_id', $this->teacher_id);
    }
}
