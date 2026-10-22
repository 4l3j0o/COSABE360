<?php

namespace App\Services;

use App\Models\ConductaReporte;
use App\Models\Registration;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * HU-82: alcance del módulo de Conducta.
 *
 * Vistas:
 *  - mios:       reportes creados por el propio docente.
 *  - paralelo:   todos los reportes del paralelo que tiene a cargo como tutor.
 *  - general:    Administrador/Regente (todo) o Director (solo su nivel).
 *  - estudiante: historial de un estudiante (Estudiante: solo el propio).
 *
 * Secretaría no tiene acceso al módulo.
 */
class ConductaScopeService
{
    public const VISTAS = ['mios', 'paralelo', 'general', 'estudiante'];

    public function tieneAcceso(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Director', 'Regente', 'Docente', 'Estudiante']);
    }

    public function esAdmin(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public function esDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    public function esRegente(User $user): bool
    {
        return $user->hasRole('Regente');
    }

    public function esDocente(User $user): bool
    {
        return $user->hasRole('Docente');
    }

    public function esEstudiante(User $user): bool
    {
        return $user->hasRole('Estudiante');
    }

    /** ¿El docente tiene algún paralelo a cargo como tutor/encargado? */
    public function esTutor(User $user): bool
    {
        return $this->esDocente($user)
            && $user->teacher
            && $this->seccionesTutor($user)->isNotEmpty();
    }

    /** Paralelos donde el docente es tutor/encargado (sections.teacher_id). */
    public function seccionesTutor(User $user): Collection
    {
        if (! $this->esDocente($user) || ! $user->teacher) {
            return collect();
        }

        return Section::with(['grade.level', 'gestion'])
            ->where('teacher_id', $user->teacher->id)
            ->ordenadoPorNivel()
            ->get();
    }

    /** Paralelos donde el docente dicta alguna materia. */
    public function seccionesDocente(User $user): Collection
    {
        if (! $this->esDocente($user) || ! $user->teacher) {
            return collect();
        }

        return $user->teacher->sectionSubjects()
            ->with(['section.grade.level', 'section.gestion'])
            ->get()
            ->map(fn ($cs) => $cs->section)
            ->filter()
            ->unique('id')
            ->pipe(fn (Collection $secciones) => Section::ordenarPorNivel($secciones));
    }

    /** Niveles a cargo del Director (vacío = sin restricción, convención del sistema). */
    public function nivelesDirector(User $user): Collection
    {
        if (! $this->esDirector($user)) {
            return collect();
        }

        return $user->nivelesACargo()->pluck('levels.id');
    }

    /** Paralelos que puede ver un Director según sus niveles a cargo. */
    public function seccionesDirector(User $user): Collection
    {
        $niveles = $this->nivelesDirector($user);

        return Section::with(['grade.level', 'gestion'])
            ->when($niveles->isNotEmpty(), fn ($q) => $q->whereHas(
                'grade',
                fn ($g) => $g->whereIn('level_id', $niveles)
            ))
            ->ordenadoPorNivel()
            ->get();
    }

    public function vistasPermitidas(User $user): array
    {
        if ($this->esAdmin($user) || $this->esRegente($user) || $this->esDirector($user)) {
            return ['general'];
        }

        if ($this->esEstudiante($user)) {
            return ['estudiante'];
        }

        if ($this->esDocente($user)) {
            $vistas = ['mios'];
            if ($this->esTutor($user)) {
                $vistas[] = 'paralelo';
            }

            return $vistas;
        }

        return [];
    }

    public function vistaPorDefecto(User $user): ?string
    {
        if ($this->esAdmin($user) || $this->esRegente($user) || $this->esDirector($user)) {
            return 'general';
        }

        if ($this->esEstudiante($user)) {
            return 'estudiante';
        }

        if ($this->esDocente($user)) {
            return $this->esTutor($user) ? 'paralelo' : 'mios';
        }

        return null;
    }

    /**
     * Si la vista pedida no está permitida, cae a la vista por defecto del rol.
     * Cuando el URL trae un curso/materia, un docente que dicta en ese curso
     * debe abrir "mios" (aunque sea tutor de otro paralelo); si el curso es el
     * que tiene a cargo como tutor, abre "paralelo".
     */
    public function resolverVista(User $user, ?string $vista, ?int $sectionId = null, ?int $subjectId = null): string
    {
        $permitidas = $this->vistasPermitidas($user);
        if ($vista && in_array($vista, $permitidas, true)) {
            return $vista;
        }

        if ($this->esDocente($user) && $sectionId) {
            if ($this->seccionesDocente($user)->pluck('id')->contains($sectionId)) {
                return 'mios';
            }

            if ($this->seccionesTutor($user)->pluck('id')->contains($sectionId)) {
                return 'paralelo';
            }
        }

        if ($this->esDocente($user) && $subjectId) {
            return 'mios';
        }

        return $this->vistaPorDefecto($user) ?? 'estudiante';
    }

    /** Vista usada para el historial de un estudiante según el rol. */
    public function vistaParaEstudiante(User $user): string
    {
        if ($this->esTutor($user)) {
            return 'paralelo';
        }

        if ($this->esDocente($user)) {
            return 'mios';
        }

        if ($this->esEstudiante($user)) {
            return 'estudiante';
        }

        return 'general';
    }

    public function cursosVisibles(User $user, string $vista): Collection
    {
        return match ($vista) {
            'mios' => $this->seccionesDocente($user),
            'paralelo' => $this->seccionesTutor($user),
            'general' => $this->esDirector($user) && $this->nivelesDirector($user)->isNotEmpty()
                ? $this->seccionesDirector($user)
                : Section::with(['grade.level', 'gestion'])->ordenadoPorNivel()->get(),
            'estudiante' => $this->esEstudiante($user)
                ? collect(array_filter([$user->student?->activeSection()]))
                : ($this->esDirector($user) && $this->nivelesDirector($user)->isNotEmpty()
                    ? $this->seccionesDirector($user)
                    : Section::with(['grade.level', 'gestion'])->ordenadoPorNivel()->get()),
            default => collect(),
        };
    }

    public function materiasVisibles(User $user, string $vista, ?int $sectionId = null): Collection
    {
        $sectionIds = $this->cursosVisibles($user, $vista)->pluck('id');

        if ($sectionId) {
            abort_unless($sectionIds->contains((int) $sectionId), 403, 'No tiene acceso a ese curso.');
            $sectionIds = collect([(int) $sectionId]);
        }

        if ($sectionIds->isEmpty()) {
            return collect();
        }

        $q = SectionSubject::with('subject')->whereIn('section_id', $sectionIds);

        if ($vista === 'mios') {
            $q->where('teacher_id', $user->teacher?->id);
        }

        return $q->get()
            ->map(fn ($cs) => $cs->subject)
            ->filter()
            ->unique('id')
            ->sortBy('nombre')
            ->values();
    }

    public function estudiantesVisibles(User $user, string $vista, ?int $sectionId = null): Collection
    {
        if ($vista === 'estudiante' && $this->esEstudiante($user)) {
            return collect(array_filter([$user->student]));
        }

        $sectionIds = $this->cursosVisibles($user, $vista)->pluck('id');

        if ($sectionId) {
            abort_unless($sectionIds->contains((int) $sectionId), 403, 'No tiene acceso a ese curso.');
            $sectionIds = collect([(int) $sectionId]);
        }

        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return Registration::where('estado', 'ACTIVA')
            ->whereIn('section_id', $sectionIds)
            ->with('enrollment.estudiante')
            ->whereHas('enrollment.estudiante', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->get()
            ->map(fn ($r) => $r->enrollment?->estudiante)
            ->filter()
            ->unique('id')
            ->sortBy(fn ($e) => $e->apellidos.' '.$e->nombres)
            ->values();
    }

    /** Alumnos matriculados activos de un paralelo concreto. */
    public function estudiantesDelCurso(Section $section): Collection
    {
        return Registration::where('estado', 'ACTIVA')
            ->where('section_id', $section->id)
            ->with('enrollment.estudiante')
            ->whereHas('enrollment.estudiante', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->get()
            ->map(fn ($r) => $r->enrollment?->estudiante)
            ->filter()
            ->unique('id')
            ->sortBy(fn ($e) => $e->apellidos.' '.$e->nombres)
            ->values();
    }

    /**
     * Query base de reportes según la vista y el alcance del rol.
     *
     * @param  array{section_id?:int|string|null,subject_id?:int|string|null,student_id?:int|string|null,desde?:string|null,hasta?:string|null}  $filtros
     */
    public function query(User $user, string $vista, array $filtros = []): Builder
    {
        $q = ConductaReporte::query()
            ->with(['student', 'section.grade.level', 'section', 'subject', 'teacher', 'registradoPor']);

        match ($vista) {
            'mios' => $q->where('registered_by_user_id', $user->id),
            'paralelo' => $this->aplicarSecciones($q, $this->seccionesTutor($user)->pluck('id')),
            'general' => $this->aplicarAlcanceGeneral($q, $user),
            'estudiante' => $this->aplicarAlcanceEstudiante($q, $user),
            default => $q->whereRaw('1 = 0'),
        };

        if (! empty($filtros['section_id'])) {
            $q->where('section_id', (int) $filtros['section_id']);
        }

        if (! empty($filtros['subject_id'])) {
            $q->where('subject_id', (int) $filtros['subject_id']);
        }

        if (! empty($filtros['student_id'])) {
            $q->where('student_id', (int) $filtros['student_id']);
        }

        $q->entreFechas(
            $filtros['desde'] ?? null,
            $filtros['hasta'] ?? null
        );

        return $q->orderByDesc('fecha')->orderByDesc('id');
    }

    private function aplicarSecciones(Builder $q, Collection $sectionIds): void
    {
        if ($sectionIds->isEmpty()) {
            $q->whereRaw('1 = 0');

            return;
        }

        $q->whereIn('section_id', $sectionIds);
    }

    private function aplicarAlcanceGeneral(Builder $q, User $user): void
    {
        if ($this->esDirector($user) && $this->nivelesDirector($user)->isNotEmpty()) {
            $this->aplicarSecciones($q, $this->seccionesDirector($user)->pluck('id'));
        }
    }

    /**
     * Vista "por estudiante": el Estudiante solo ve lo propio; el resto usa su
     * alcance habitual (tutor: paralelo; docente: sus materias; directivos: general).
     */
    private function aplicarAlcanceEstudiante(Builder $q, User $user): void
    {
        if ($this->esEstudiante($user)) {
            $q->where('student_id', $user->student?->id ?? 0);

            return;
        }

        if ($this->esTutor($user)) {
            $this->aplicarSecciones($q, $this->seccionesTutor($user)->pluck('id'));

            return;
        }

        if ($this->esDocente($user)) {
            $q->where('registered_by_user_id', $user->id);

            return;
        }

        $this->aplicarAlcanceGeneral($q, $user);
    }

    public function puedeVer(User $user, ConductaReporte $reporte): bool
    {
        if ($this->esAdmin($user) || $this->esRegente($user)) {
            return true;
        }

        if ($this->esDirector($user)) {
            $niveles = $this->nivelesDirector($user);
            if ($niveles->isEmpty()) {
                return true;
            }

            return $niveles->contains($reporte->section?->grade?->level_id);
        }

        if ($this->esDocente($user) && $user->teacher) {
            if ((int) $reporte->registered_by_user_id === (int) $user->id) {
                return true;
            }

            return $this->seccionesTutor($user)->pluck('id')->contains($reporte->section_id);
        }

        if ($this->esEstudiante($user) && $user->student) {
            return (int) $reporte->student_id === (int) $user->student->id;
        }

        return false;
    }

    public function puedeCrear(User $user, Section $section, ?int $subjectId = null): bool
    {
        if ($this->esAdmin($user) || $this->esRegente($user)) {
            return true;
        }

        if ($this->esDirector($user)) {
            return $this->seccionesDirector($user)->pluck('id')->contains($section->id);
        }

        if (! $this->esDocente($user) || ! $user->teacher) {
            return false;
        }

        if ($this->seccionesTutor($user)->pluck('id')->contains($section->id)) {
            return true;
        }

        $q = SectionSubject::where('teacher_id', $user->teacher->id)
            ->where('section_id', $section->id);

        if ($subjectId) {
            $q->where('subject_id', $subjectId);
        }

        return $q->exists();
    }

    /** Paralelos donde el usuario puede registrar reportes. */
    public function cursosCreables(User $user): Collection
    {
        if ($this->esAdmin($user) || $this->esRegente($user)) {
            return Section::with(['grade.level', 'gestion'])
                ->where('estado', 'ACTIVO')
                ->ordenadoPorNivel()
                ->get();
        }

        if ($this->esDirector($user)) {
            return $this->seccionesDirector($user)->where('estado', 'ACTIVO')->values();
        }

        if ($this->esDocente($user)) {
            return $this->seccionesDocente($user)
                ->merge($this->seccionesTutor($user))
                ->unique('id')
                ->where('estado', 'ACTIVO')
                ->pipe(fn (Collection $secciones) => Section::ordenarPorNivel($secciones));
        }

        return collect();
    }

    /** Materias que puede elegir al crear un reporte en un paralelo. */
    public function materiasCreables(User $user, Section $section): Collection
    {
        if ($this->esDocente($user) && $user->teacher
            && ! $this->seccionesTutor($user)->pluck('id')->contains($section->id)) {
            $ids = SectionSubject::where('teacher_id', $user->teacher->id)
                ->where('section_id', $section->id)
                ->pluck('subject_id')
                ->unique();

            return $ids->isEmpty()
                ? collect()
                : Subject::whereIn('id', $ids)->orderBy('nombre')->get();
        }

        return Subject::whereHas('sectionSubjects', fn ($q) => $q->where('section_id', $section->id))
            ->orderBy('nombre')
            ->get();
    }

    public function teacherIdPara(User $user, Section $section, ?int $subjectId = null): ?int
    {
        if ($this->esDocente($user) && $user->teacher) {
            return $user->teacher->id;
        }

        return SectionSubject::where('section_id', $section->id)
            ->when($subjectId, fn ($q, $id) => $q->where('subject_id', $id))
            ->value('teacher_id');
    }
}
