<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_id',
        'horas_semanales',
        'estado',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'nivel_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'materia_id');
    }
}
