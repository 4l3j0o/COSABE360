<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class, 'course_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'curso_id');
    }
}
