<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'sigla',
        'area',
        'grupo',
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

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'subject_id');
    }

    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class, 'subject_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'subject_id');
    }
}
