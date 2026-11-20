<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxConfiguration extends Model
{
    protected $fillable = [
        'nit_colegio',
        'razon_social',
        'tipo_entidad',
        'exento_iva_educacion',
        'exento_it',
        'iue_exento',
        'modalidad_sfe',
        'cuis',
        'codigo_sucursal',
        'agente_retencion_rc_iva',
        'leyenda_iva_exento',
        'leyenda_it_exento',
        'leyenda_ley070',
        'monto_mensualidad',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'exento_iva_educacion' => 'boolean',
            'exento_it' => 'boolean',
            'iue_exento' => 'boolean',
            'agente_retencion_rc_iva' => 'boolean',
            'monto_mensualidad' => 'decimal:2',
        ];
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
