<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiarioAulaRequest;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\DiarioAula;
use App\Models\Registration;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\ImagenWebp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\ArchivoPrivado;
use Illuminate\View\View;

class DiarioAulaController extends Controller
{
    /**
     * HU-65/HU-82: bitácora de aula — el docente anota avance curricular,
     * actividades de clase, tareas para el hogar, recursos y observaciones;
     * estudiantes, padres y directores la consultan.
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
        $contexto = $request->boolean('contexto') && $sectionId !== null;

        $section = $sectionId ? Section::with(['grade.level', 'gestion'])->find($sectionId) : null;
        if ($section && ! $this->permiteCurso($user, $section->id, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        $materiasPorCurso = $this->materiasPorCurso($user, $cursos);

        // Si el curso tiene una sola materia visible para el docente, se
        // preselecciona automáticamente (igual que en Conducta).
        if (! $subjectId && $sectionId && count($materiasPorCurso[$sectionId] ?? []) === 1) {
            $subjectId = (int) $materiasPorCurso[$sectionId][0];
        }

        $materias = $this->materiasVisibles($user, $sectionId, $cursos);
        if ($subjectId && ! $materias->contains('id', (int) $subjectId)) {
            $subjectId = null;
        }

        // Docente y estudiante siempre trabajan sobre un curso concreto
        $requiereCurso = $this->requiereCurso($user);
        $entradas = collect();
        if (! $requiereCurso || $sectionId) {
            $entradas = DiarioAula::with([
                'section.grade.level', 'section', 'subject', 'teacher',
                'actividades', 'tareas', 'recursos',
            ])
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId),
                    fn ($q) => $q->whereIn('section_id', $cursos->pluck('id')))
                ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();
        }

        $sectionActual = $sectionId ? $cursos->firstWhere('id', (int) $sectionId) : null;
        $materiaActual = $subjectId ? $materias->firstWhere('id', (int) $subjectId) : null;

        return view('diario-aula.index', [
            'cursos' => $cursos,
            'materias' => $materias,
            'section' => $section,
            'entradas' => $entradas,
            'requiereCurso' => $requiereCurso && ! $sectionId,
            'filtros' => [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
            ],
            'contexto' => $contexto,
            'sectionActual' => $sectionActual,
            'materiaActual' => $materiaActual,
            'materiasPorCurso' => $materiasPorCurso,
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
            'contexto' => 1,
        ]);
    }

    /**
     * Formulario de nueva bitácora (docente y roles administrativos).
     */
    public function create(Request $request): View
    {
        $this->authorizeRegistro($request->user());

        $contexto = $request->boolean('contexto');
        $sectionId = $request->query('section_id');
        $cursos = $this->cursosRegistro($request->user());
        $section = $sectionId
            ? $cursos->firstWhere('id', (int) $sectionId)
            : ($cursos->count() === 1 ? $cursos->first() : null);

        abort_if($sectionId && ! $section, 403, 'No tiene acceso a ese curso.');

        $materias = $section ? $this->materiasRegistro($request->user(), $section) : collect();

        $subjectId = $request->query('subject_id')
            ?: ($materias->count() === 1 ? $materias->first()->id : null);

        if ($subjectId && ! $materias->contains('id', (int) $subjectId)) {
            abort(403, 'No tiene acceso a esa materia.');
        }

        // Si no hay materia definida, se muestra el selector normal aunque
        // venga marcado como contexto de curso.
        $contexto = $contexto && $subjectId !== null;

        return view('diario-aula.create', [
            'cursos' => $cursos,
            'section' => $section,
            'materias' => $materias,
            'subjectId' => $subjectId,
            'contexto' => $contexto,
            'hoy' => now()->toDateString(),
        ]);
    }

    public function store(StoreDiarioAulaRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeRegistro($user);

        $data = $request->validated();
        $section = Section::findOrFail($data['section_id']);
        $this->authorizeDocenteDelCurso($user, $section);
        $this->authorizeMateria($user, $section, (int) $data['subject_id']);

        $diario = DB::transaction(function () use ($request, $data, $section, $user) {
            $diario = DiarioAula::create([
                'section_id' => $section->id,
                'subject_id' => $data['subject_id'],
                'teacher_id' => $this->teacherIdPara($user, $section),
                'fecha' => $data['fecha'],
                'avance_curricular' => $data['avance_curricular'],
                'observaciones' => $data['observaciones'] ?? null,
                'registered_by_user_id' => $user->id,
            ]);

            $this->guardarActividades($diario, $request->input('actividades', []));
            $this->guardarTareas($diario, $request->input('tareas', []));
            $this->guardarRecursos($diario, $request);

            return $diario;
        });

        return redirect()->route('diario.index', [
            'section_id' => $section->id,
            'subject_id' => $diario->subject_id,
            'contexto' => $request->boolean('contexto') ? 1 : null,
        ])->with('mensaje', 'Bitácora registrada en el diario de aula.');
    }

    public function show(Request $request, DiarioAula $diario): View
    {
        $user = $request->user();
        if (! $this->permiteCurso($user, $diario->section_id, $this->cursosPermitidos($user))) {
            abort(403, 'No tiene acceso a esta bitácora.');
        }
        $diario->load(['section.grade.level', 'section', 'subject', 'teacher', 'actividades', 'tareas', 'recursos']);

        return view('diario-aula.show', ['entrada' => $diario]);
    }

    public function destroy(Request $request, DiarioAula $diario): RedirectResponse
    {
        $user = $request->user();

        $puede = $user->puede('diario.eliminar') && (
            $user->hasRole('Administrador')
            || ($user->hasRole('Director') && $this->permiteCurso($user, $diario->section_id, $this->cursosPermitidos($user)))
            || ($user->hasRole('Docente') && $user->teacher && $diario->teacher_id === $user->teacher->id)
        );

        abort_unless($puede, 403, 'Solo el autor o la dirección puede eliminar esta bitácora.');

        $sectionId = $diario->section_id;
        $subjectId = $diario->subject_id;

        foreach ($diario->recursos as $recurso) {
            if ($recurso->imagen_path) {
                ArchivoPrivado::eliminar($recurso->imagen_path);
            }
        }

        $diario->delete();

        return redirect()->route('diario.index', ['section_id' => $sectionId, 'subject_id' => $subjectId])
            ->with('mensaje', 'Bitácora eliminada.');
    }

    // ---------- detalle de la bitácora ----------

    /** @return array<int, array<int, int>> */
    private function materiasPorCurso($user, $cursos): array
    {
        $sectionIds = $cursos->pluck('id');

        if ($sectionIds->isEmpty()) {
            return [];
        }

        $q = SectionSubject::whereIn('section_id', $sectionIds);

        if ($user->hasRole('Docente') && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        }

        return $q->get(['section_id', 'subject_id'])
            ->groupBy('section_id')
            ->map(fn ($grupo) => $grupo->pluck('subject_id')->unique()->values()->all())
            ->all();
    }

    private function guardarActividades(DiarioAula $diario, array $filas): void
    {
        $orden = 0;

        foreach ($filas as $fila) {
            if (! is_array($fila) || $this->filaVacia($fila)) {
                continue;
            }

            $diario->actividades()->create([
                'tema_estudio' => $this->limpiar($fila['tema_estudio'] ?? null),
                'referencia_bibliografica' => $this->limpiar($fila['referencia_bibliografica'] ?? null),
                'descripcion' => $this->limpiar($fila['descripcion'] ?? null),
                'orden' => $orden++,
            ]);
        }
    }

    private function guardarTareas(DiarioAula $diario, array $filas): void
    {
        $orden = 0;

        foreach ($filas as $fila) {
            if (! is_array($fila) || $this->filaVacia($fila)) {
                continue;
            }

            $diario->tareas()->create([
                'paginas_referencia' => $this->limpiar($fila['paginas_referencia'] ?? null),
                'forma_presentacion' => $this->limpiar($fila['forma_presentacion'] ?? null),
                'fecha' => $this->limpiar($fila['fecha'] ?? null),
                'descripcion' => $this->limpiar($fila['descripcion'] ?? null),
                'orden' => $orden++,
            ]);
        }
    }

    private function guardarRecursos(DiarioAula $diario, Request $request): void
    {
        $filas = $request->input('recursos', []);
        $archivos = $request->file('recursos', []);
        $orden = 0;

        foreach ($filas as $i => $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $nombre = $this->limpiar($fila['nombre'] ?? null);
            $link = $this->limpiar($fila['link'] ?? null);
            $imagen = $archivos[$i]['imagen'] ?? null;

            if ($nombre === null && $link === null && ! $imagen) {
                continue;
            }

            $path = $imagen
                ? app(ImagenWebp::class)->guardar($imagen, 'diario/'.$diario->id, 'rec_')
                : null;

            $diario->recursos()->create([
                'nombre' => $nombre,
                'imagen_path' => $path,
                'link' => $link,
                'orden' => $orden++,
            ]);
        }
    }

    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (is_string($valor) ? trim($valor) !== '' : ! is_null($valor)) {
                return false;
            }
        }

        return true;
    }

    private function limpiar($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
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
            $user->puede('diario.crear'),
            403, 'Solo docentes y directivos registran el diario.'
        );
    }

    private function authorizeDocenteDelCurso($user, Section $section): void
    {
        if ($user->hasRole('Administrador')) {
            return;
        }

        if ($user->hasRole('Director')) {
            abort_unless(
                $this->permiteCurso($user, $section->id, $this->cursosPermitidos($user)),
                403, 'No tiene acceso a ese curso.'
            );

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
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
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
        if ($user->hasRole('Administrador')) {
            return Section::with(['grade.level', 'gestion'])->ordenadoPorNivel()->get();
        }

        if ($user->hasRole('Director')) {
            $niveles = $user->nivelesACargo()->pluck('levels.id');

            return Section::with(['grade.level', 'gestion'])
                ->when($niveles->isNotEmpty(), fn ($q) => $q->whereHas(
                    'grade',
                    fn ($g) => $g->whereIn('level_id', $niveles)
                ))
                ->ordenadoPorNivel()
                ->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->sectionSubjects()
                ->with(['section.grade.level', 'section', 'section.gestion'])
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
        if ($user->hasRole('Administrador')) {
            return Section::with(['grade'])->where('estado', 'ACTIVO')->ordenadoPorNivel()->get();
        }

        if ($user->hasRole('Director')) {
            return $this->cursosPermitidos($user)->where('estado', 'ACTIVO')->values();
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
        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasRole('Director')) {
            return $cursos->contains('id', $sectionId);
        }

        return $cursos->contains('id', $sectionId);
    }
}
