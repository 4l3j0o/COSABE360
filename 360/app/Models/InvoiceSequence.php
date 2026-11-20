<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceSequence extends Model
{
    protected $fillable = [
        'codigo_sucursal',
        'punto_venta',
        'ultimo_numero',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_numero' => 'integer',
        ];
    }

    /**
     * Obtiene y incrementa atómicamente el siguiente número de factura.
     * Uso: $nro = InvoiceSequence::siguienteNumero('0','0');
     */
    public static function siguienteNumero(string $sucursal = '0', string $puntoVenta = '0'): int
    {
        return DB::transaction(function () use ($sucursal, $puntoVenta) {
            $seq = static::lockForUpdate()
                ->firstOrCreate(
                    ['codigo_sucursal' => $sucursal, 'punto_venta' => $puntoVenta],
                    ['ultimo_numero' => 0, 'estado' => 'ACTIVO']
                );
            $seq->increment('ultimo_numero');
            return $seq->fresh()->ultimo_numero;
        });
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }
}
