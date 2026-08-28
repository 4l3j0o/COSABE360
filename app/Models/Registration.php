<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    public const ESTADOS = ['ACTIVA', 'INACTIVA', 'CANCELADA'];

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'fecha',
        'matriculation_cost',
        'monto_mensual',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'matriculation_cost' => 'decimal:2',
        'monto_mensual' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
