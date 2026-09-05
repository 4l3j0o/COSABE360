<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignificantEvent extends Model
{
    protected $fillable = [
        'tipo_evento',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'documentos_soporte',
        'cufd_inicio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public const ESTADOS = ['ABIERTO', 'CERRADO', 'REPORTADO'];

    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'ABIERTO');
    }

    public function estaAbierto(): bool
    {
        return $this->estado === 'ABIERTO';
    }
}
