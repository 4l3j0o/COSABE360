<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'horas_semanales', 'estado'];

    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class);
    }

    public function courses(): HasMany
    {
        return $this->hasManyThrough(Course::class, CourseSubject::class);
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
