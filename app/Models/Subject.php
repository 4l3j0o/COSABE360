<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'materia_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'materia_id');
    }
}
