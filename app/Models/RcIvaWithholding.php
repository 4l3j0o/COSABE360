<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RcIvaWithholding extends Model
{
    protected $fillable = [
        'fecha',
        'tipo_sujeto',
        'sujeto_id',
        'sujeto_nombre',
        'sujeto_documento',
        'nro_factura_origen',
        'base_imponible',
        'retencion_monto',
        'comprobante_nro',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'sujeto_id' => 'integer',
            'base_imponible' => 'decimal:2',
            'retencion_monto' => 'decimal:2',
        ];
    }

    public const ESTADOS = ['REGISTRADA', 'CONFIRMADA', 'ANULADA'];
    public const TIPOS_SUJETO = ['EMPLEADO', 'PROVEEDOR'];

    /**
     * Calcula retención RC-IVA 13% según normativa boliviana.
     */
    public static function calcularRetencion(float $baseImponible, float $porcentaje = 13.0): float
    {
        return round($baseImponible * ($porcentaje / 100), 2);
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'CONFIRMADA');
    }
}
