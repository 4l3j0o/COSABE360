<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPayment extends Model
{
    public const ESTADOS = ['PENDIENTE', 'PAGO_PARCIAL', 'PAGADO', 'PAGADA', 'VENCIDO', 'ESPERA'];
    public const METODOS_PAGO = ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA', 'OTRO'];

    protected $fillable = [
        'registration_id',
        'electronic_invoice_id',
        'mes',
        'anio',
        'monto_esperado',
        'monto_pagado',
        'monto',
        'fecha_pago',
        'fecha',
        'metodo_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'mes' => 'integer',
        'anio' => 'integer',
        'monto_esperado' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
        'fecha' => 'date',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function electronicInvoice(): BelongsTo
    {
        return $this->belongsTo(ElectronicInvoice::class, 'electronic_invoice_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function estaPagada(): bool
    {
        return in_array($this->estado, ['PAGADO', 'PAGADA']);
    }

    public function estaEnEspera(): bool
    {
        return $this->estado === 'ESPERA';
    }
}
