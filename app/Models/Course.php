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
     * Orden pedagógico: primero Inicial, luego Primaria, luego Secundaria;
     * dentro del nivel por grado y luego por paralelo.
     * Usa subconsultas para no chocar con los where() existentes.
     */
    public function scopeOrdenadoPorNivel($query): void
    {
        $query->orderByRaw('(select levels.orden from levels inner join grades g1 on g1.level_id = levels.id where g1.id = courses.grade_id limit 1)')
            ->orderByRaw('(select g2.orden from grades g2 where g2.id = courses.grade_id limit 1)')
            ->orderByRaw('(select s.codigo from sections s where s.id = courses.section_id limit 1)');
    }

    /** Misma ordenación para colecciones ya cargadas (listas de docente, etc.). */
    public static function ordenarPorNivel(\Illuminate\Support\Collection $cursos): \Illuminate\Support\Collection
    {
        return $cursos->sortBy([
            fn ($c) => $c->grade?->level?->orden ?? 999,
            fn ($c) => $c->grade?->orden ?? 999,
            fn ($c) => $c->section?->codigo ?? $c->section?->nombre ?? $c->nombre,
        ])->values();
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
