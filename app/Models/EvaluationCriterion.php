<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriterion extends Model
{
    /**
     * Criterios oficiales R.M. 473/2021 / R.M. 001/2022 (configurables por colegio).
     * SE=SER, SA=SABER, HA=HACER, AU=AUTOEVALUACIÓN.
     */
    public const CRITERIOS_POR_DEFECTO = [
        ['codigo' => 'SE', 'nombre' => 'SER', 'porcentaje' => 10, 'tipo' => 'VALOR_UNICO'],
        ['codigo' => 'SA', 'nombre' => 'SABER', 'porcentaje' => 45, 'tipo' => 'ACTIVIDADES'],
        ['codigo' => 'HA', 'nombre' => 'HACER', 'porcentaje' => 40, 'tipo' => 'ACTIVIDADES'],
        ['codigo' => 'AU', 'nombre' => 'AUTOEVALUACIÓN', 'porcentaje' => 5, 'tipo' => 'VALOR_UNICO'],
    ];

    protected $fillable = [
        'evaluation_plan_id',
        'codigo',
        'nombre',
        'porcentaje',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(EvaluationPlan::class, 'evaluation_plan_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(EvaluationActivity::class, 'evaluation_criteria_id');
    }
}
