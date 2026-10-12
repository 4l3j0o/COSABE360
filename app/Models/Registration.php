<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    public const ESTADOS = ['ACTIVA', 'INACTIVA', 'CANCELADA'];

    protected $fillable = [
        'enrollment_id',
        'section_id',
        'fecha',
        'monto_mensual',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_mensual' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function monthlyPayments(): HasMany
    {
        return $this->hasMany(MonthlyPayment::class, 'registration_id');
    }
}
