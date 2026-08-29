<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const ESTADOS = ['PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO'];

    protected $fillable = [
        'student_id',
        'course_id',
        'fecha',
        'estado',
        'justificacion',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function scopeDelCurso($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeDeFecha($query, string $fecha)
    {
        return $query->where('fecha', $fecha);
    }
}
