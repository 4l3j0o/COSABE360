<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * HU-69: el paralelo (section) es la unidad-clase.
 * Absorbe lo que antes era el "curso": grado + gestión (academic_year) + aula.
 */
class Section extends Model
{
    protected $fillable = [
        'grade_id',
        'academic_year_id',
        'nombre',
        'codigo',
        'teacher_id',
        'capacidad',
        'aula',
        'estado',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Orden pedagógico: Inicial, Primaria, Secundaria; luego grado y código.
     */
    public function scopeOrdenadoPorNivel($query): void
    {
        $query->orderByRaw('(select levels.orden from levels inner join grades g1 on g1.level_id = levels.id where g1.id = sections.grade_id limit 1)')
            ->orderByRaw('(select g2.orden from grades g2 where g2.id = sections.grade_id limit 1)')
            ->orderBy('codigo');
    }

    /** Misma ordenación para colecciones ya cargadas (listas de docente, etc.).
     *  Ojo: sortBy con arreglo de closures los trata como comparadores, no
     *  como claves; por eso se encadena de menor a mayor significancia. */
    public static function ordenarPorNivel(\Illuminate\Support\Collection $secciones): \Illuminate\Support\Collection
    {
        return $secciones
            ->sortBy(fn ($s) => $s->codigo ?? $s->nombre)
            ->sortBy(fn ($s) => $s->grade?->orden ?? 999)
            ->sortBy(fn ($s) => $s->grade?->level?->orden ?? 999)
            ->values();
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    /**
     * Código corto para vista (Ej: I1A · 2026).
     */
    public function codigoCorto(): string
    {
        $codigo = $this->codigo ?: (string) $this->nombre;
        $gestion = $this->gestion?->nombre;

        return $gestion ? $codigo.' · '.$gestion : $codigo;
    }

    /**
     * Código con extensión (S5A-BQS). El sufijo vive en el nombre
     * ("A - BQS"); si no hay, devuelve el código base.
     */
    public function codigoCompleto(): string
    {
        $codigo = (string) ($this->codigo ?? $this->nombre ?? '');

        if (preg_match('/-\s*([A-Za-z0-9]+)\s*$/', (string) $this->nombre, $m)) {
            $sufijo = strtoupper($m[1]);
            if (! str_ends_with(strtoupper($codigo), '-' . $sufijo)) {
                return $codigo . '-' . $sufijo;
            }
        }

        return $codigo;
    }

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'section_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'section_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'section_id');
    }

    /**
     * Estudiantes matriculados (Registration ACTIVA) en este paralelo.
     */
    public function students()
    {
        return Student::whereHas('enrollments', function ($q) {
            $q->where('enrollments.estado', 'COMPLETADA')
                ->whereHas('registration', function ($r) {
                    $r->where('section_id', $this->id)
                        ->where('registrations.estado', 'ACTIVA');
                });
        });
    }
}
