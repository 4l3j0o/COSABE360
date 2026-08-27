<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScore extends Model
{
    protected $fillable = ['assessment_id', 'student_id', 'nota', 'observaciones', 'registrado_por', 'registrado_en'];

    protected $casts = [
        'nota' => 'decimal:2',
        'registrado_en' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
