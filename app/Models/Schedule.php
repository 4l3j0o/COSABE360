<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'curso_id',
        'materia_id',
        'docente_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i:s',
        'hora_fin' => 'datetime:H:i:s',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'docente_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'materia_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'curso_id');
    }
}
