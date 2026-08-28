<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['section_id', 'nombre', 'gestion', 'estado'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class, 'course_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'course_id');
    }

    public function students(): HasMany
    {
        return $this->hasManyThrough(Student::class, Enrollment::class);
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
