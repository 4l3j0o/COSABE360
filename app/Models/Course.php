<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'curso_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'curso_id');
    }
}
