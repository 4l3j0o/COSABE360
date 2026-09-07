<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ArcoRequest extends Model
{
    public const TIPOS = ['ACCESO', 'RECTIFICACION', 'CANCELACION', 'OPOSICION'];

    public const ESTADOS = ['RECIBIDA', 'EN_PROCESO', 'RESUELTA', 'RECHAZADA'];

    protected $fillable = [
        'titular_type',
        'titular_id',
        'tipo',
        'detalle',
        'estado',
        'fecha_solicitud',
        'fecha_resolucion',
        'resolucion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_resolucion' => 'date',
        ];
    }

    public function titular(): MorphTo
    {
        return $this->morphTo();
    }
}
