<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConductaReporte extends Model
{
    protected $table = 'conducta_reportes';

    protected $fillable = [
        'student_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'fecha',
        'hora',
        'descripcion',
        'registered_by_user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    /** HU-82: reportes creados por un usuario concreto (vista "Mis reportes"). */
    public function scopeDelUsuario(Builder $query, int $userId): void
    {
        $query->where('registered_by_user_id', $userId);
    }

    /** HU-82: reportes de un paralelo. */
    public function scopeDeSeccion(Builder $query, int $sectionId): void
    {
        $query->where('section_id', $sectionId);
    }

    /** HU-82: reportes de un estudiante. */
    public function scopeDeEstudiante(Builder $query, int $studentId): void
    {
        $query->where('student_id', $studentId);
    }

    public function scopeEntreFechas(Builder $query, ?string $desde, ?string $hasta): void
    {
        $query->when($desde, fn (Builder $q, $fecha) => $q->where('fecha', '>=', $fecha))
            ->when($hasta, fn (Builder $q, $fecha) => $q->where('fecha', '<=', $fecha));
    }
}
