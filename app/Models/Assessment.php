<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = ['course_subject_id', 'nombre', 'tipo', 'bimestre', 'peso', 'fecha', 'descripcion', 'estado'];

    protected $casts = ['peso' => 'decimal:2', 'fecha' => 'date'];

    public function courseSubject(): BelongsTo
    {
        return $this->belongsTo(CourseSubject::class, 'course_subject_id');
    }

    public function gradeScores(): HasMany
    {
        return $this->hasMany(GradeScore::class);
    }
}
