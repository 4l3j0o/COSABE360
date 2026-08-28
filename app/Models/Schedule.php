<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'curso_id',
        'materia_id',
        'docente_id',
        'course_id',
        'subject_id',
        'teacher_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i:s',
        'hora_fin' => 'datetime:H:i:s',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'curso_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'materia_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'docente_id');
    }

    public function courseEn(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function subjectEn(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacherEn(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
