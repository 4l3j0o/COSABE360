<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationSecondAttempt extends Model
{
    public const ESTADOS = ['HABILITADO', 'RENDIDO', 'APROBADO', 'REPROBADO'];

    protected $fillable = [
        'evaluation_plan_id',
        'student_id',
        'nota_original',
        'nota_recuperacion',
        'estado',
        'fecha',
        'registered_by_user_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'nota_original' => 'decimal:2',
            'nota_recuperacion' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(EvaluationPlan::class, 'evaluation_plan_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function aprobado(): bool
    {
        return $this->nota_recuperacion !== null && $this->nota_recuperacion >= 51;
    }
}
