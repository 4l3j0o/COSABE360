<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalService extends Model
{
    protected $fillable = [
        'codigo_sin',
        'descripcion',
        'unidad_medida',
        'iva_tipo',
        'iva_tasa',
        'it_tipo',
        'it_tasa',
        'leyenda_factura',
        'aplica_exento_educacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'iva_tasa' => 'decimal:2',
            'it_tasa' => 'decimal:2',
            'aplica_exento_educacion' => 'boolean',
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }
}
