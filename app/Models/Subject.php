<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
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

    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'materia_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'materia_id');
    }
}
