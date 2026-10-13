<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConductaReporte extends Model
{
    protected $table = 'conducta_reportes';

    /** Tipos de falta válidos (HU-65). */
    public const TIPOS = ['LEVE', 'GRAVE', 'MUY_GRAVE'];

    public const TIPOS_LABEL = [
        'LEVE' => 'Leve',
        'GRAVE' => 'Grave',
        'MUY_GRAVE' => 'Muy grave',
    ];

    protected $fillable = [
        'student_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'tipo_falta',
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

    public function tipoLabel(): string
    {
        return self::TIPOS_LABEL[$this->tipo_falta] ?? $this->tipo_falta;
    }
}
