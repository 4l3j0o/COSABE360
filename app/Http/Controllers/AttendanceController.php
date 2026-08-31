<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Lista de cursos del docente para registrar asistencia.
     */
    public function index(): View
    {
        $user = auth()->user();
        $cursos = collect();
        $cursosRegistro = collect();

        $puede = false;
        $base = null;

        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $cursoIds = CourseSubject::where('teacher_id', $teacher->id)
                    ->pluck('course_id')
                    ->unique()
                    ->values();

                $base = Course::whereIn('estado', ['ACTIVO', 'INACTIVO'])->whereIn('id', $cursoIds)
                    ->with(['grade', 'section', 'gestion'])
                    ->orderBy('nombre');
                $puede = true;
            }
        } elseif ($user->hasAnyRole('Director,Administrador,Secretaría')) {
            $base = Course::whereIn('estado', ['ACTIVO', 'INACTIVO'])
                ->with(['grade', 'section', 'gestion'])
                ->orderBy('nombre');
            $puede = true;
        }

        if ($puede) {
            $cursos = (clone $base)->paginate(10)->withQueryString();
            $cursosRegistro = (clone $base)->where('estado', 'ACTIVO')->paginate(10, ['*'], 'pageRegistro')->withQueryString();
        }

        $materiasPorCurso = $cursosRegistro instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $cursosRegistro->getCollection()->mapWithKeys(fn (Course $curso) => [$curso->id => $this->materiasDeCurso($curso)])
            : $cursosRegistro->mapWithKeys(fn (Course $curso) => [$curso->id => $this->materiasDeCurso($curso)]);

        $grados = \App\Models\Grade::with('level')->orderBy('orden')->orderBy('nombre')->get();
        $paralelos = \App\Models\Section::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $gestiones = \App\Models\AcademicYear::orderByDesc('nombre')->get();

        return view('attendances.index', compact('cursos', 'cursosRegistro', 'materiasPorCurso', 'grados', 'paralelos', 'gestiones'));
    }

    /**
     * Formulario de registro diario para un curso.
     */
    public function create(Request $request): View
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'subject_id' => ['nullable', 'integer'],
            'fecha' => ['nullable', 'date'],
        ]);

        $course = Course::with(['grade', 'section', 'gestion'])->findOrFail($request->query('course_id'));

        // El docente solo registra la asistencia del día actual; las demás fechas son para roles administrativos.
        $user = auth()->user();
        $fecha = $user->hasRole('Docente')
            ? now()->toDateString()
            : ($request->query('fecha') ? Carbon::parse($request->query('fecha'))->toDateString() : now()->toDateString());

        $this->authorizeDocenteDelCurso($course);

        $materias = $this->materiasDeCurso($course);
        $subjectId = $this->resolverMateriaOLaPrimera($request, $materias);

        $estudiantes = $this->estudiantesDelCurso($course);
        $asistenciasExistentes = Attendance::where('course_id', $course->id)
            ->whereDate('fecha', $fecha)
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId), fn ($q) => $q->whereNull('subject_id'))
            ->get()
            ->keyBy('student_id');

        $editar = $request->boolean('editar');

        return view('attendances.create', compact('course', 'fecha', 'estudiantes', 'asistenciasExistentes', 'editar', 'materias', 'subjectId'));
    }

    /**
     * Guarda/actualiza la asistencia diaria en bloque.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'subject_id' => ['nullable', 'integer'],
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estado' => ['required', 'in:PRESENTE,FALTA,ATRASO,PERMISO,JUSTIFICADO'],
            'asistencias.*.justificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $this->authorizeDocenteDelCurso($course);

        $estudiantes = $this->estudiantesDelCurso($course)->pluck('id')->toArray();

        // El docente solo puede guardar la asistencia del día actual.
        $user = auth()->user();
        $fechaNormalizada = $user->hasRole('Docente')
            ? now()->toDateString()
            : \Carbon\Carbon::parse($validated['fecha'])->toDateString();

        $materias = $this->materiasDeCurso($course);
        $subjectId = $this->resolverMateriaOLaPrimera($request, $materias);

        foreach ($validated['asistencias'] as $studentId => $data) {
            if (!in_array((int) $studentId, $estudiantes, true)) {
                continue;
            }

            $existente = Attendance::where('student_id', $studentId)
                ->where('course_id', $course->id)
                ->whereDate('fecha', $fechaNormalizada)
                ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId), fn ($q) => $q->whereNull('subject_id'))
                ->first();

            if ($existente) {
                $existente->update([
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            } else {
                Attendance::create([
                    'student_id' => $studentId,
                    'course_id' => $course->id,
                    'subject_id' => $subjectId,
                    'fecha' => $fechaNormalizada,
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('asistencias.index')->with('success', 'Asistencia registrada correctamente para ' . $fechaNormalizada);
    }

    /**
     * Historial (usado también por HU-27, pero expuesto aquí para reutilizar).
     */
    public function historial(Request $request): View
    {
        $user = auth()->user();
        $materiaFiltro = $request->query('materia');
        $studentId = $request->query('student_id');
        $fechaDesde = $request->query('desde');
        $fechaHasta = $request->query('hasta');
        $bimestre = $request->query('bimestre');

        // Estudiantes a los que el usuario tiene acceso (según rol)
        $estudiantesDisponibles = collect();

        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $cursoIds = CourseSubject::where('teacher_id', $teacher->id)->pluck('course_id')->unique();
                $estudiantesDisponibles = Student::where('estado', 'ACTIVO')
                    ->whereHas('enrollments.registration', fn ($q) => $q->whereIn('course_id', $cursoIds)->where('registrations.estado', 'ACTIVA'))
                    ->orderBy('apellidos')->orderBy('nombres')->get();
            }
        } elseif ($user->hasRole('Estudiante')) {
            $estudiantesDisponibles = Student::where('user_id', $user->id)->where('estado', 'ACTIVO')->get();
        } elseif ($user->hasRole('Padre/Tutor') || $user->hasAnyRole('Padre/Tutor')) {
            $parent = \App\Models\ParentGuardian::where('user_id', $user->id)->first();
            $estudiantesDisponibles = $parent
                ? $parent->students()->where('students.estado', 'ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get()
                : collect();
        } else {
            $estudiantesDisponibles = Student::where('estado', 'ACTIVO')
                ->orderBy('apellidos')->orderBy('nombres')->get();
        }

        // Forzar selección dentro de los estudiantes permitidos
        $permitidos = $estudiantesDisponibles->pluck('id');
        $studentSeleccionado = $studentId && $permitidos->contains((int) $studentId)
            ? $estudiantesDisponibles->firstWhere('id', (int) $studentId)
            : $estudiantesDisponibles->first();

        $query = Attendance::with(['student', 'course.grade', 'course.section', 'subject'])
            ->whereIn('student_id', $permitidos)
            ->orderByDesc('fecha');

        // Filtro por rol base: siempre restringe a los estudiantes permitidos
        if ($studentSeleccionado) {
            $query->where('student_id', $studentSeleccionado->id);
        }
        if ($materiaFiltro) {
            if ($materiaFiltro === 'General') {
                $query->whereNull('subject_id');
            } else {
                $query->whereHas('subject', fn ($q) => $q->where('nombre', $materiaFiltro));
            }
        }
        if ($fechaDesde) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->where('fecha', '<=', $fechaHasta);
        }
        if ($bimestre) {
            $periodo = $this->rangoBimestre((int) $bimestre);
            $query->whereBetween('fecha', $periodo);
        }

        $asistencias = $query->paginate(10)->withQueryString();

        // Materias del estudiante seleccionado (de sus cursos activos)
        $materias = collect();
        if ($studentSeleccionado) {
            $cursoIdsEst = $studentSeleccionado->registrations()
                ->where('registrations.estado', 'ACTIVA')
                ->pluck('course_id');
            if ($cursoIdsEst->isNotEmpty()) {
                $materias = Subject::whereIn('id', CourseSubject::whereIn('course_id', $cursoIdsEst)->pluck('subject_id'))
                    ->orderBy('nombre')->get();
            }
        }

        // Estadísticas: mismas restricciones de rol y filtros, sobre los estudiantes permitidos
        $estadisticasQuery = Attendance::query()
            ->whereIn('student_id', $permitidos)
            ->when($studentSeleccionado, fn ($q) => $q->where('student_id', $studentSeleccionado->id))
            ->when($materiaFiltro, function ($q) use ($materiaFiltro) {
                $materiaFiltro === 'General'
                    ? $q->whereNull('subject_id')
                    : $q->whereHas('subject', fn ($sq) => $sq->where('nombre', $materiaFiltro));
            })
            ->when($fechaDesde, fn ($q) => $q->where('fecha', '>=', $fechaDesde))
            ->when($fechaHasta, fn ($q) => $q->where('fecha', '<=', $fechaHasta))
            ->when($bimestre, fn ($q) => $q->whereBetween('fecha', $this->rangoBimestre((int) $bimestre)));

        $estadisticas = $estadisticasQuery
            ->selectRaw("estado, COUNT(*) as total")
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('attendances.historial', compact(
            'asistencias',
            'estudiantesDisponibles',
            'studentSeleccionado',
            'materias',
            'estadisticas',
            'materiaFiltro',
            'studentId',
            'fechaDesde',
            'fechaHasta',
            'bimestre'
        ));
    }

    /**
     * Rango de fechas (inicio/fin) de un bimestre según la gestión académica activa.
     * Cada bimestre abarca 3 meses desde la fecha de inicio de la gestión.
     */
    private function rangoBimestre(int $bimestre): array
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();
        $inicio = $gestion && $gestion->fecha_inicio
            ? Carbon::parse($gestion->fecha_inicio)
            : Carbon::create(Carbon::now()->year, 2, 1);

        $b = max(1, min(4, $bimestre));
        $ini = $inicio->copy()->addMonths(($b - 1) * 3)->startOfMonth();
        $fin = $inicio->copy()->addMonths($b * 3)->startOfMonth()->subDay();

        return [$ini->format('Y-m-d'), $fin->format('Y-m-d')];
    }

/**
     * Asistencia de un curso del docente: vista por mes de cada estudiante,
     * ordenada por nombre (por defecto el mes actual).
     */
    public function porCurso(Request $request, CourseSubject $courseSubject): View
    {
        $teacher = $request->user()->teacher;
        abort_if (! $teacher || $teacher->id !== $courseSubject->teacher_id, 403);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion', 'schedules']);

        $course = $courseSubject->course;

        $anio = $course->gestion?->fecha_inicio?->year ?? now()->year;
        $mesIni = $course->gestion?->fecha_inicio?->month ?? 2;
        $mesFin = $course->gestion?->fecha_fin?->month ?? 11;
        $mesIni = max((int) $mesIni, 2);
        $mesFin = min((int) $mesFin, 11);
        $mesesDisponibles = $mesIni <= $mesFin ? range($mesIni, $mesFin) : range(2, 11);

        $mesPorDefecto = in_array((int) now()->month, $mesesDisponibles, true)
            ? (int) now()->month
            : $mesesDisponibles[0];

        $mesStr = (string) $request->input('mes', $anio . '-' . str_pad((string) $mesPorDefecto, 2, '0', STR_PAD_LEFT));
        $mesStr = preg_match('/^(\d{4})-(\d{2})$/', $mesStr, $coincide) && in_array((int) $coincide[2], $mesesDisponibles, true)
            ? $mesStr
            : $anio . '-' . str_pad((string) $mesPorDefecto, 2, '0', STR_PAD_LEFT);
        $inicioMes = Carbon::createFromFormat('Y-m-d', $mesStr . '-15');
        $anio = (int) $inicioMes->year;
        $mes = (int) $inicioMes->month;

        $students = $course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $asistencias = Attendance::where('course_id', $course->id)
            ->where('subject_id', $courseSubject->subject_id)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->whereIn('student_id', $students->pluck('id'))
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $fechas = $asistencias
            ->map(fn (Attendance $a) => $a->fecha->toDateString())
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $d) => Carbon::parse($d));

        $porEstudiante = $asistencias->groupBy('student_id')->map(function ($regs) {
            return $regs->keyBy(fn (Attendance $r) => $r->fecha->toDateString());
        });

        $filas = $students->map(fn (Student $s) => [
            'student' => $s,
            'asistencias' => $porEstudiante->get($s->id, collect()),
        ])->values();

        $dias = $courseSubject->schedules
            ->pluck('dia')
            ->unique()
            ->values();

        return view('attendances.por-curso', compact(
            'courseSubject',
            'course',
            'students',
            'filas',
            'fechas',
            'mesStr',
            'anio',
            'mes',
            'mesesDisponibles',
            'dias'
        ));
    }

    private function estudiantesDelCurso(Course $course)
    {
        // Estudiantes con matrícula ACTIVA en el curso
        return Student::whereHas('enrollments.registration', function ($q) use ($course) {
            $q->where('course_id', $course->id)->where('registrations.estado', 'ACTIVA');
        })->where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    /**
     * Materias disponibles de un curso según el rol: para el docente solo las que él imparte;
     * para roles administrativos todas las materias del curso.
     */
    private function materiasDeCurso(Course $course)
    {
        $user = auth()->user();
        $materias = CourseSubject::where('course_id', $course->id);

        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return collect();
            }
            $materias->where('teacher_id', $teacher->id);
        }

        $ids = $materias->pluck('subject_id')->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return Subject::whereIn('id', $ids)->orderBy('nombre')->get();
    }

    /**
     * Devuelve el subject_id pedido si pertenece a las materias del curso, si no la primera.
     */
    private function resolverMateriaOLaPrimera(Request $request, $materias): ?int
    {
        $subjectId = $request->integer('subject_id') ?: null;
        if ($subjectId && !$materias->contains('id', $subjectId)) {
            $subjectId = null;
        }

        return $subjectId ?? $materias->first()?->id;
    }

    private function authorizeDocenteDelCurso(Course $course): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole('Administrador,Director,Secretaría')) {
            return;
        }

        if ($user->hasRole('Docente')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                abort(403, 'No tiene perfil docente.');
            }
            $esResponsable = CourseSubject::where('teacher_id', $teacher->id)
                ->where('course_id', $course->id)
                ->exists();

            // También permitir si es responsable del paralelo
            $esResponsableParalelo = \App\Models\Section::where('id', $course->section_id)
                ->where('teacher_id', $teacher->id)
                ->exists();

            if (!$esResponsable && !$esResponsableParalelo) {
                abort(403, 'No está asignado a este curso.');
            }
            return;
        }

        abort(403);
    }
}
