<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiarioAulaRequest;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\DiarioAula;
use App\Models\Registration;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiarioAulaController extends Controller
{
    /**
     * HU-65: bitácora de aula — el docente anota avance curricular,
     * ejercicios y observaciones; estudiantes, padres y directores la consultan.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);

        if (! $request->query('section_id') && $cursos->count() === 1) {
            $request->merge(['section_id' => $cursos->first()->id]);
        }

        $sectionId = $request->query('section_id');
        $subjectId = $request->query('subject_id');

        $section = $sectionId ? Section::with(['grade', 'gestion'])->find($sectionId) : null;
        if ($section && ! $this->permiteCurso($user, $section->id, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        $materias = $this->materiasVisibles($user, $sectionId, $cursos);
        if ($subjectId && ! $materias->contains('id', (int) $subjectId)) {
            $subjectId = null;
        }

        // Docente y estudiante siempre trabajan sobre un curso concreto
        $requiereCurso = $this->requiereCurso($user);
        $entradas = collect();
        if (! $requiereCurso || $sectionId) {
            $entradas = DiarioAula::with(['section.grade', 'section', 'subject', 'teacher'])
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId),
                    fn ($q) => $q->whereIn('section_id', $cursos->pluck('id')))
                ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();
        }

        return view('diario-aula.index', [
            'cursos' => $cursos,
            'materias' => $materias,
            'section' => $section,
            'entradas' => $entradas,
            'requiereCurso' => $requiereCurso && ! $sectionId,
            'filtros' => $request->only(['section_id', 'subject_id']),
        ]);
    }

    /**
     * HU-65c: acción rápida — abre el diario de la clase actual según horario;
     * fuera de horario avisa y lleva a Mis Cursos.
     */
    public function ahora(): RedirectResponse
    {
        $bloque = $this->bloqueActual();

        if (! $bloque) {
            return redirect()->route('docente.cursos')
                ->with('mensaje', 'Fuera de tu horario de clases. Elige el curso para abrir su diario de aula.');
        }

        return redirect()->route('diario.index', [
            'section_id' => $bloque->section_id,
            'subject_id' => $bloque->subject_id,
        ]);
    }

    /**
     * Formulario de nueva bitácora (docente y roles administrativos).
     */
    public function create(Request $request): View
    {
        $this->authorizeRegistro($request->user());

        $sectionId = $request->query('section_id');
        $cursos = $this->cursosRegistro($request->user());
        $section = $sectionId
            ? $cursos->firstWhere('id', (int) $sectionId)
            : ($cursos->count() === 1 ? $cursos->first() : null);

        $materias = $section ? $this->materiasRegistro($request->user(), $section) : collect();

        return view('diario-aula.create', [
            'cursos' => $cursos,
            'section' => $section,
            'materias' => $materias,
            'hoy' => now()->toDateString(),
        ]);
    }

    public function store(StoreDiarioAulaRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeRegistro($user);

        $section = Section::findOrFail($request->validated()['section_id']);
        $this->authorizeDocenteDelCurso($user, $section);

        $data = $request->validated();
        $this->authorizeMateria($user, $section, (int) $data['subject_id']);

        DiarioAula::create($data + [
            'teacher_id' => $this->teacherIdPara($user, $section),
            'registered_by_user_id' => $user->id,
        ]);

        return redirect()->route('diario.index', ['section_id' => $section->id])
            ->with('mensaje', 'Bitácora registrada en el diario de aula.');
    }

    public function show(Request $request, DiarioAula $diario): View
    {
        $user = $request->user();
        if (! $this->permiteCurso($user, $diario->section_id, $this->cursosPermitidos($user))) {
            abort(403, 'No tiene acceso a esta bitácora.');
        }
        $diario->load(['section.grade', 'section', 'subject', 'teacher']);

        return view('diario-aula.show', ['entrada' => $diario]);
    }

    public function destroy(Request $request, DiarioAula $diario): RedirectResponse
    {
        $user = $request->user();

        $puede = $user->hasAnyRole(['Administrador', 'Director'])
            || ($user->hasRole('Docente') && $user->teacher && $diario->teacher_id === $user->teacher->id);

        abort_unless($puede, 403, 'Solo el autor o la dirección puede eliminar esta bitácora.');

        $sectionId = $diario->section_id;
        $diario->delete();

        return redirect()->route('diario.index', ['section_id' => $sectionId])
            ->with('mensaje', 'Bitácora eliminada.');
    }

    // ---------- permisos y alcance ----------

    private function bloqueActual()
    {
        $teacher = auth()->user()->teacher
            ?? Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return null;
        }

        $ahora = \Carbon\Carbon::now('America/La_Paz');
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO'];
        $dia = $dias[$ahora->dayOfWeekIso] ?? null;
        if (! $dia) {
            return null;
        }

        $hora = $ahora->format('H:i:s');

        return \App\Models\Schedule::where('teacher_id', $teacher->id)
            ->where('dia', $dia)
            ->where('estado', 'ACTIVO')
            ->whereNotNull('section_id')
            ->orderBy('hora_inicio')
            ->get()
            ->first(fn ($s) => $s->hora_inicio && $s->hora_fin
                && $s->hora_inicio->format('H:i:s') <= $hora
                && $hora <= $s->hora_fin->format('H:i:s'));
    }

    private function authorizeRegistro($user): void
    {
        abort_unless(
            $user->hasAnyRole(['Docente', 'Administrador', 'Director', 'Secretaría']),
            403, 'Solo docentes y personal administrativo registran el diario.'
        );
    }

    private function authorizeDocenteDelCurso($user, Section $section): void
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return;
        }
        if ($user->hasRole('Docente') && $user->teacher) {
            $asignado = SectionSubject::where('teacher_id', $user->teacher->id)
                ->where('section_id', $section->id)->exists()
                || \App\Models\Section::where('id', $section->section_id)
                    ->where('teacher_id', $user->teacher->id)->exists();
            if ($asignado) {
                return;
            }
        }
        abort(403, 'No está asignado a este curso.');
    }

    private function authorizeMateria($user, Section $section, int $subjectId): void
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return;
        }
        $ok = SectionSubject::where('section_id', $section->id)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $user->teacher->id)
            ->exists();
        abort_unless($ok, 403, 'Esa materia no le corresponde en este curso.');
    }

    private function teacherIdPara($user, Section $section): ?int
    {
        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->id;
        }
        // Registro administrativo: docente titular de la materia si existe
        return SectionSubject::where('section_id', $section->id)
            ->where('subject_id', request('subject_id'))
            ->value('teacher_id');
    }

    private function cursosPermitidos($user)
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return Section::with(['grade', 'gestion'])->ordenadoPorNivel()->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->sectionSubjects()
                ->with(['section.grade', 'section', 'section.gestion'])
                ->get()->map(fn ($cs) => $cs->section)->unique('id')->pipe(fn ($c) => Section::ordenarPorNivel($c));
        }

        if ($user->hasRole('Estudiante') && $user->student) {
            $curso = $user->student->activeSection();

            return $curso ? collect([$curso]) : collect();
        }

        return collect();
    }

    private function cursosRegistro($user)
    {
        // Para registrar: el docente solo sus cursos; admin ve todos los activos
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return Section::with(['grade'])->where('estado', 'ACTIVO')->ordenadoPorNivel()->get();
        }

        return $this->cursosPermitidos($user)->where('estado', 'ACTIVO')->values();
    }

    private function materiasVisibles($user, $sectionId, $cursos)
    {
        $q = SectionSubject::with('subject');
        if ($sectionId) {
            $q->where('section_id', $sectionId);
        } else {
            $q->whereIn('section_id', $cursos->pluck('id'));
        }
        if ($user->hasRole('Docente') && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        }

        return $q->get()->map(fn ($cs) => $cs->subject)->unique('id')->values();
    }

    private function materiasRegistro($user, Section $section)
    {
        $q = SectionSubject::where('section_id', $section->id);
        if ($user->hasRole('Docente') && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        }
        $ids = $q->pluck('subject_id')->unique()->values();

        return $ids->isEmpty() ? collect() : Subject::whereIn('id', $ids)->orderBy('nombre')->get();
    }

    private function requiereCurso($user): bool
    {
        return $user->hasRole('Docente') || $user->hasRole('Estudiante');
    }

    private function permiteCurso($user, int $sectionId, $cursos): bool
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return true;
        }

        return $cursos->contains('id', $sectionId);
    }
}
