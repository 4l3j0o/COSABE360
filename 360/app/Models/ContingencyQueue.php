<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContingencyQueue extends Model
{
    protected $fillable = [
        'electronic_invoice_id',
        'intentos',
        'max_intentos',
        'estado',
        'ultimo_error',
        'proximo_intento',
    ];

    protected function casts(): array
    {
        return [
            'intentos' => 'integer',
            'max_intentos' => 'integer',
            'proximo_intento' => 'datetime',
        ];
    }

    public const ESTADOS = ['PENDIENTE', 'ENVIANDO', 'COMPLETADO', 'FALLIDO'];

    public function electronicInvoice(): BelongsTo
    {
        return $this->belongsTo(ElectronicInvoice::class, 'electronic_invoice_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE')
            ->where(function ($q) {
                $q->whereNull('proximo_intento')->orWhere('proximo_intento', '<=', now());
            });
    }

    public function puedeReintentar(): bool
    {
        return $this->intentos < $this->max_intentos && $this->estado !== 'COMPLETADO';
    }
}
