<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Assessment extends Model
{
    protected $fillable = [
        'section_subject_id',
        'subject_id',
        'academic_year_id',
        'nombre',
        'tipo',
        'bimestre',
        'peso',
        'fecha',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'peso' => 'decimal:2',
            'fecha' => 'date',
            'bimestre' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function sectionSubject(): BelongsTo
    {
        return $this->belongsTo(SectionSubject::class, 'section_subject_id');
    }

    public function gradeScores(): HasMany
    {
        return $this->hasMany(GradeScore::class, 'assessment_id');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
