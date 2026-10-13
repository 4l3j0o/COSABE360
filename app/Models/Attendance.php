<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const ESTADOS = ['PRESENTE', 'AUSENTE', 'LICENCIA', 'RETRASO', 'RETIRO_JUSTIFICADO'];

    /** Letras cortas para los chips de la lista (P/A/L/R/RJ). */
    public const LETRAS = [
        'PRESENTE' => 'P',
        'AUSENTE' => 'A',
        'LICENCIA' => 'L',
        'RETRASO' => 'R',
        'RETIRO_JUSTIFICADO' => 'RJ',
    ];

    protected $fillable = [
        'student_id',
        'section_id',
        'subject_id',
        'fecha',
        'estado',
        'justificacion',
        'registered_by_user_id',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function scopeDelCurso($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    public function scopeDeFecha($query, string $fecha)
    {
        return $query->where('fecha', $fecha);
    }
}
