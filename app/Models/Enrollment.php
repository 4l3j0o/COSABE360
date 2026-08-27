<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = ['student_id', 'course_id', 'gestion', 'fecha_inscripcion', 'estado'];

    protected $casts = [
        'fecha_inscripcion' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }
}
