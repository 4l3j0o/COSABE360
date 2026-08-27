<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'materia_id',
        'gestion_id',
        'peso',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'peso' => 'float',
            'fecha' => 'date',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'materia_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'gestion_id');
    }

    public function gradeScores(): HasMany
    {
        return $this->hasMany(GradeScore::class, 'evaluacion_id');
    }
}
