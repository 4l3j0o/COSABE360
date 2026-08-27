<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'docente_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'docente_id');
    }
}
