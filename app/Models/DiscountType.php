<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountType extends Model
{
    public const APLICACIONES = [
        'PORCENTAJE' => 'Porcentaje del Importe Base',
        'IMPORTE_FIJO' => 'Importe Fijo a Descontar',
        'IMPORTE_MANUAL' => 'Importe Manual',
    ];

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_aplicacion',
        'porcentaje',
        'importe_fijo',
        'orden',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
            'importe_fijo' => 'decimal:2',
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    /**
     * Calcula el monto de descuento para un importe base dado.
     */
    public function montoDescuento(float $importeBase): float
    {
        return match ($this->tipo_aplicacion) {
            'PORCENTAJE' => round($importeBase * (float) $this->porcentaje / 100, 2),
            'IMPORTE_FIJO' => round((float) $this->importe_fijo, 2),
            default => 0.0,
        };
    }
}
