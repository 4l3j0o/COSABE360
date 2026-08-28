<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'fecha',
        'estado',
        'observaciones',
        'grade_id',
        'parallel_id',
        'status',
        'monthly_fee',
    ];

    protected $casts = [
        'fecha' => 'date',
        'academic_year_id' => 'integer',
        'monthly_fee' => 'decimal:2',
        'status' => 'string',
    ];

    public const ESTADOS = ['PENDIENTE', 'COMPLETADA', 'ANULADA'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function parallel(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'parallel_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class, 'enrollment_id');
    }

    public function monthlyPayments(): HasMany
    {
        return $this->hasMany(MonthlyPayment::class, 'enrollment_id');
    }

    // CA-19-02: saldo pendiente = suma de cuotas PENDIENTES
    public function getSaldoPendienteAttribute(): float
    {
        return (float) $this->monthlyPayments()
            ->where('estado', 'PENDIENTE')
            ->sum('monto');
    }
}
