<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationPlan extends Model
{
    public const TRIMESTRES = [1, 2, 3];

    public const ESTADOS = ['BORRADOR', 'ACTIVO', 'CERRADO'];

    protected $fillable = [
        'course_subject_id',
        'trimestre',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'trimestre' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function courseSubject(): BelongsTo
    {
        return $this->belongsTo(CourseSubject::class, 'course_subject_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriterion::class, 'evaluation_plan_id');
    }

    /**
     * Criterio por código (SE, SA, HA, AU).
     */
    public function criterio(string $codigo): ?EvaluationCriterion
    {
        return $this->criteria()->where('codigo', $codigo)->first();
    }

    /**
     * Crea los 4 criterios oficiales con sus porcentajes por defecto.
     */
    public function crearCriteriosPorDefecto(): void
    {
        $defecto = EvaluationCriterion::CRITERIOS_POR_DEFECTO;

        foreach ($defecto as $c) {
            $this->criteria()->firstOrCreate(
                ['codigo' => $c['codigo']],
                $c
            );
        }
    }
}
