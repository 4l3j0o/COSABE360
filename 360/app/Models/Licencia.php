<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HU-82: licencia de un estudiante (expediente con motivo, evidencia y
 * comunicado automático). Se refleja en las listas de asistencia por materia.
 */
class Licencia extends Model
{
    protected $table = 'licencias';

    public const ESTADOS = ['ACTIVA', 'ANULADA'];

    protected $fillable = [
        'student_id',
        'section_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'evidencia_path',
        'announcement_id',
        'estado',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
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

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'ACTIVA';
    }
}
