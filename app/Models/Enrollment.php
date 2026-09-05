<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'fecha',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'academic_year_id' => 'integer',
    ];

    public const ESTADOS = ['PENDIENTE', 'COMPLETADA', 'ANULADA'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class, 'enrollment_id');
    }
}
