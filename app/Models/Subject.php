<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'level_id',
        'horas_semanales',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'horas_semanales' => 'integer',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

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
