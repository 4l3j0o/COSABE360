<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationActivity extends Model
{
    protected $fillable = [
        'evaluation_criteria_id',
        'nombre',
        'orden',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'evaluation_criteria_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(EvaluationActivityGrade::class, 'evaluation_activity_id');
    }

    /**
     * Nota de un estudiante en esta actividad (o null si aún no la tiene).
     */
    public function notaDe(Student $student): ?float
    {
        $grade = $this->grades()->where('student_id', $student->id)->first();

        return $grade ? (float) $grade->nota : null;
    }
}
