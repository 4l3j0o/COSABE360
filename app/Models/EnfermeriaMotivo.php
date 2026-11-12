<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnfermeriaMotivo extends Model
{
    protected $table = 'enfermeria_motivos';

    protected $fillable = ['nombre', 'activo', 'orden'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function atenciones(): HasMany
    {
        return $this->hasMany(EnfermeriaAtencion::class, 'motivo_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }
}
