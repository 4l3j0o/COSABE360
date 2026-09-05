<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCufd extends Model
{
    protected $fillable = [
        'cufd',
        'codigo_control',
        'fecha_vigencia',
        'motivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vigencia' => 'date',
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function scopeVigente($query)
    {
        return $query->where('estado', 'ACTIVO')
            ->whereDate('fecha_vigencia', '>=', now()->toDateString());
    }

    public function estaVigente(): bool
    {
        return $this->estado === 'ACTIVO' && $this->fecha_vigencia >= now()->toDateString();
    }
}
