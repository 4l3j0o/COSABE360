<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Level;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * HU-65: alcance de comunicados (quién crea para quién y quién ve qué).
 *
 * Creación:
 * - Administrador: todo alcance.
 * - Director sin niveles a cargo: todo alcance (compatibilidad).
 * - Director con niveles: solo NIVEL_DOCENTES (sus niveles) y CURSO_ESPECIFICO (cursos de sus niveles).
 * - Docente: solo CURSO_ESPECIFICO de los cursos donde dicta o de los que es tutor.
 *
 * Lectura (bandeja, solo PUBLICADO):
 * - Administrador y Director: ven todo.
 * - Resto: según el destinatario del comunicado (TODOS, por rol, por curso o por nivel).
 */
class ComunicadoAlcanceService
{
    public const TIPO_NIVEL = 'NIVEL_DOCENTES';

    /** Tipos que el usuario puede usar al crear (valor => etiqueta). */
    public function tiposPermitidos(User $user): array
    {
        if ($user->esAdmin()) {
            return $this->etiquetas(Announcement::TIPOS_DESTINATARIO);
        }

        if ($user->hasRole('Director')) {
            if ($user->nivelesACargo()->exists()) {
                return $this->etiquetas(['NIVEL_DOCENTES', 'CURSO_ESPECIFICO']);
            }

            return $this->etiquetas(Announcement::TIPOS_DESTINATARIO);
        }

        if ($user->hasRole('Docente')) {
            return $this->etiquetas(['CURSO_ESPECIFICO']);
        }

        return [];
    }

    /** Cursos ofrecibles en el selector según el rol. */
    public function cursosCreables(User $user): Collection
    {
        $query = Section::with(['grade.level'])->where('estado', 'ACTIVO')->ordenadoPorNivel();

        if ($user->esAdmin()) {
            return $query->get();
        }

        if ($user->hasRole('Director')) {
            $niveles = $user->nivelesACargo()->pluck('levels.id');
            if ($niveles->isEmpty()) {
                return $query->get();
            }

            return $query->whereHas('grade', fn ($q) => $q->whereIn('level_id', $niveles))->get();
        }

        if ($user->hasRole('Docente')) {
            $ids = $this->cursosIdsDocente($user);
            if ($ids->isEmpty()) {
                return collect();
            }

            return $query->whereIn('sections.id', $ids)->get();
        }

        return collect();
    }

    /** Niveles ofrecibles para NIVEL_DOCENTES. */
    public function nivelesCreables(User $user): Collection
    {
        if ($user->esAdmin()) {
            return Level::orderBy('id')->get();
        }

        if ($user->hasRole('Director')) {
            $niveles = $user->nivelesACargo()->orderBy('levels.id')->get();
            if ($niveles->isNotEmpty()) {
                return $niveles;
            }

            return Level::orderBy('id')->get();
        }

        return collect();
    }

    /**
     * Valida que el alcance elegido esté permitido para el usuario.
     * Retorna mensaje de error o null si es válido.
     */
    public function validarAlcance(User $user, string $tipo, ?int $sectionId, ?int $levelId): ?string
    {
        if (! array_key_exists($tipo, $this->tiposPermitidos($user))) {
            return 'No tienes permiso para enviar a ese destinatario.';
        }

        if ($tipo === 'CURSO_ESPECIFICO') {
            if (! $sectionId) {
                return 'Debe seleccionar un curso.';
            }
            if (! $user->esAdmin() && ! $this->cursosCreables($user)->contains('id', $sectionId)) {
                if ($user->hasRole('Director')) {
                    return 'El curso no pertenece a tu nivel a cargo.';
                }

                return 'Solo puedes enviar a cursos donde dictas clases o eres tutor.';
            }
        }

        if ($tipo === self::TIPO_NIVEL) {
            if (! $levelId) {
                return 'Debe seleccionar un nivel.';
            }
            if (! $this->nivelesCreables($user)->contains('id', $levelId)) {
                return 'Ese nivel no está a tu cargo.';
            }
        }

        return null;
    }

    /** ¿El comunicado publicado es visible para el usuario en su bandeja? */
    public function visiblePara(User $user, Announcement $announcement): bool
    {
        if ($user->esAdmin() || $user->hasRole('Director')) {
            return true;
        }

        $recipient = $announcement->recipients->first();
        if (! $recipient) {
            return true;
        }

        return match ($recipient->tipo_destinatario) {
            'TODOS' => true,
            'DOCENTES' => $user->hasRole('Docente'),
            'ESTUDIANTES' => $user->hasRole('Estudiante'),
            'CURSO_ESPECIFICO' => $recipient->section_id !== null && $this->esMiembroDelCurso($user, $recipient->section_id),
            self::TIPO_NIVEL => $recipient->level_id !== null && $this->esDocenteDelNivel($user, $recipient->level_id),
            default => false,
        };
    }

    /**
     * Bandeja del usuario (publicados visibles para él), más recientes primero.
     */
    public function bandejaPara(User $user, ?int $limite = null): Collection
    {
        $todos = Announcement::publicados()
            ->with(['author', 'recipients', 'author.rol'])
            ->get()
            ->filter(fn ($a) => $this->visiblePara($user, $a))
            ->values();

        return $limite !== null ? $todos->take($limite)->values() : $todos;
    }

    /**
     * Destacados sin leer para el pantallazo al ingresar.
     */
    public function destacadosPara(User $user): Collection
    {
        $leidos = \App\Models\AnnouncementRead::where('user_id', $user->id)
            ->where('leido', true)
            ->pluck('announcement_id')
            ->all();

        return $this->bandejaPara($user)
            ->filter(fn ($a) => $a->destacado && ! in_array($a->id, $leidos, true))
            ->values();
    }

    /**
     * Sin leer para la campana/stat: publicados visibles sin fila de
     * lectura (o marcada no leída).
     */
    public function noLeidosPara(User $user): int
    {
        $leidos = \App\Models\AnnouncementRead::where('user_id', $user->id)
            ->where('leido', true)
            ->pluck('announcement_id')
            ->all();

        return $this->bandejaPara($user)
            ->filter(fn ($a) => ! in_array($a->id, $leidos, true))
            ->count();
    }

    /** Marca es_nuevo en cada comunicado según lecturas del usuario. */
    public function marcarNovedad(User $user, iterable $announcements): void
    {
        $ids = collect($announcements)->pluck('id')->all();
        if ($ids === []) {
            return;
        }

        $leidos = \App\Models\AnnouncementRead::where('user_id', $user->id)
            ->whereIn('announcement_id', $ids)
            ->pluck('announcement_id')
            ->all();

        foreach ($announcements as $announcement) {
            $announcement->setAttribute('es_nuevo', ! in_array($announcement->id, $leidos, true));
        }
    }

    /** Cursos donde el docente dicta o es tutor de paralelo. */
    public function cursosIdsDocente(User $user): Collection
    {
        $teacherId = $user->teacher?->id;
        if (! $teacherId) {
            return collect();
        }

        $porMateria = SectionSubject::where('teacher_id', $teacherId)->pluck('section_id');
        $secciones = Section::where('teacher_id', $teacherId)->pluck('id');
        $porTutoria = $secciones->isNotEmpty()
            ? Section::whereIn('section_id', $secciones)->pluck('id')
            : collect();

        return $porMateria->merge($porTutoria)->unique()->values();
    }

    /** Niveles donde el docente dicta o es tutor. */
    public function nivelesIdsDocente(User $user): Collection
    {
        $cursos = $this->cursosIdsDocente($user);
        if ($cursos->isEmpty()) {
            return collect();
        }

        return Section::whereIn('sections.id', $cursos)
            ->join('grades', 'grades.id', '=', 'sections.grade_id')
            ->distinct()
            ->pluck('grades.level_id');
    }

    private function esMiembroDelCurso(User $user, int $sectionId): bool
    {
        // Estudiante matriculado en el curso
        if ($user->hasRole('Estudiante') && $user->student) {
            if ($this->cursosIdsEstudiante($user)->contains($sectionId)) {
                return true;
            }
        }

        // Docente del curso (dicta o tutor)
        if ($user->hasRole('Docente') && $this->cursosIdsDocente($user)->contains($sectionId)) {
            return true;
        }

        // Secretaría/Regente no filtran por curso: ven avisos de curso para operar
        if ($user->hasRole('Secretaría') || $user->hasRole('Regente')) {
            return true;
        }

        return false;
    }

    private function esDocenteDelNivel(User $user, int $levelId): bool
    {
        return $user->hasRole('Docente') && $this->nivelesIdsDocente($user)->contains($levelId);
    }

    private function cursosIdsEstudiante(User $user): Collection
    {
        $student = $user->student;
        if (! $student) {
            return collect();
        }

        $ids = $student->registrations()->where('registrations.estado', 'ACTIVA')->pluck('registrations.section_id');
        if ($student->activeSection()) {
            $ids->push($student->activeSection()->id);
        }

        return $ids->unique()->values();
    }

    private function etiquetas(array $tipos): array
    {
        $labels = [
            'TODOS' => 'Todos',
            'DOCENTES' => 'Docentes',
            'ESTUDIANTES' => 'Estudiantes',
            'CURSO_ESPECIFICO' => 'Curso específico',
            'NIVEL_DOCENTES' => 'Docentes de un nivel',
        ];

        $out = [];
        foreach ($tipos as $tipo) {
            $out[$tipo] = $labels[$tipo] ?? $tipo;
        }

        return $out;
    }
}
