<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'grade_id',        // grado (nivel educativo)
        'parallel_id',     // paralelo (A, B, C, etc.)
        'academic_year_id',// año académico
        'status',          // Pendiente, Matriculado, Anulado
        'matriculation_cost', // costo de matrícula
        'monthly_fee',     // mensualidad
    ];

    protected $casts = [
        'academic_year_id' => 'integer',
        'matriculation_cost' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'status' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function parallel()
    {
        return $this->belongsTo(Parallel::class, 'parallel_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
