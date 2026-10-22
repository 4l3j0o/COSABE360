<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioAula extends Model
{
    protected $table = 'diario_aulas';

    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'fecha',
        'avance_curricular',
        'actividades_clase',
        'actividades_hogar',
        'recursos',
        'observaciones',
        'registered_by_user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

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
}
