<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCurricularAdaptation extends Model
{
    public const CONDICIONES = [
        'DISCAPACIDAD' => 'Discapacidad',
        'DIFICULTAD_APRENDIZAJE' => 'Dificultad de Aprendizaje',
        'TALENTO_EXTRAORDINARIO' => 'Talento Extraordinario',
    ];

    protected $fillable = [
        'student_id',
        'condicion',
        'tipo_discapacidad',
        'grado',
        'descripcion',
        'plan_adaptacion',
        'modalidad',
        'evaluacion_diferenciada',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'evaluacion_diferenciada' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVO');
    }
}
