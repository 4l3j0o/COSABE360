<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['grado_id', 'nombre', 'responsable_id', 'capacidad', 'estado'];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grado_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'responsable_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'paralelo_id');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
