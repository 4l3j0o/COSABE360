<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPayment extends Model
{
    public const ESTADOS = ['PENDIENTE', 'PAGADA'];

    public const METODOS_PAGO = ['EFECTIVO', 'TRANSFERENCIA', 'TARJETA', 'OTRO'];

    protected $fillable = [
        'enrollment_id',
        'mes',
        'monto',
        'fecha',
        'metodo_pago',
        'estado',
    ];

    protected $casts = [
        'mes' => 'integer',
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}
