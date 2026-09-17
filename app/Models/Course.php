<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'grade_id',
        'section_id',
        'academic_year_id',
        'nombre',
        'aula',
        'estado',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Código corto del paralelo para vista (Ej: I1A, P2C, S5A).
     * Usa Section.codigo; si no existe, cae al nombre completo.
     */
    public function codigoCorto(): string
    {
        $codigo = $this->section?->codigo;

        if (! empty($codigo)) {
            $gestion = $this->gestion?->nombre;

            return $gestion ? $codigo.' · '.$gestion : $codigo;
        }

        return (string) $this->nombre;
    }

    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class, 'course_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'course_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'course_id');
    }

    /**
     * Estudiantes matriculados (Registration ACTIVA) en este paralelo.
     * Cadena: Student -> Enrollment (COMPLETADA) -> Registration (ACTIVA, course_id).
     */
    public function students()
    {
        return Student::whereHas('enrollments', function ($q) {
            $q->where('enrollments.estado', 'COMPLETADA')
                ->whereHas('registration', function ($r) {
                    $r->where('course_id', $this->id)
                        ->where('registrations.estado', 'ACTIVA');
                });
        });
    }
}
