<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSubject extends Model
{
    protected $fillable = [
        'materia_id',
        'curso_id',
        'docente_id',
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

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'materia_id', 'materia_id')
            ->where('curso_id', $this->curso_id)
            ->where('docente_id', $this->docente_id);
    }
}
