<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Licencia;
use App\Models\Section;
use App\Models\SectionSubject;
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
                $cursoIds = SectionSubject::where('teacher_id', $teacher->id)
                    ->pluck('section_id')
                    ->unique()
                    ->values();

                $base = Section::whereIn('estado', ['ACTIVO', 'INACTIVO'])->whereIn('id', $cursoIds)
                    ->with(['grade', 'gestion'])
                    ->ordenadoPorNivel();
                $puede = true;
            }
        } elseif ($user->hasAnyRole('Director,Administrador,Secretaría')) {
            $base = Section::whereIn('estado', ['ACTIVO', 'INACTIVO'])
                ->with(['grade', 'gestion'])
                ->ordenadoPorNivel();
            $puede = true;
        }

        if ($puede) {
            $cursos = (clone $base)->paginate(10)->withQueryString();
            $cursosRegistro = (clone $base)->where('estado', 'ACTIVO')->paginate(10, ['*'], 'pageRegistro')->withQueryString();
        }

        $materiasPorCurso = $cursosRegistro instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $cursosRegistro->getCollection()->mapWithKeys(fn (Section $curso) => [$curso->id => $this->materiasDeCurso($curso)])
            : $cursosRegistro->mapWithKeys(fn (Section $curso) => [$curso->id => $this->materiasDeCurso($curso)]);

        $grados = \App\Models\Grade::with('level')->where('estado', '!=', 'ELIMINADO')->orderBy('orden')->orderBy('nombre')->get();
        $paralelos = \App\Models\Section::where('estado', 'ACTIVO')->ordenadoPorNivel()->get();
        $gestiones = \App\Models\AcademicYear::orderByDesc('nombre')->get();

        return view('attendances.index', compact('cursos', 'cursosRegistro', 'materiasPorCurso', 'grados', 'paralelos', 'gestiones'));
    }

    /**
     * Lleva al docente directo a pasar lista del curso que está dictando ahora,
     * según su horario (día + bloque horario en hora de Bolivia).
     * Sin clase en curso, redirige a Mis Cursos para elegir.
     */
    public function ahora(): RedirectResponse
    {
        $teacher = auth()->user()->teacher
            ?? Teacher::where('user_id', auth()->id())->first();

        abort_unless($teacher, 403);

        $ahora = Carbon::now('America/La_Paz');
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES'];
        $dia = $dias[$ahora->dayOfWeekIso] ?? null;

        if ($dia) {
            $hora = $ahora->format('H:i:s');
            $bloque = \App\Models\Schedule::where('teacher_id', $teacher->id)
                ->where('dia', $dia)
                ->where('estado', 'ACTIVO')
                ->whereNotNull('section_id')
                ->orderBy('hora_inicio')
                ->get()
                ->first(fn ($s) => $s->hora_inicio && $s->hora_fin
                    && $s->hora_inicio->format('H:i:s') <= $hora
                    && $hora <= $s->hora_fin->format('H:i:s'));

            if ($bloque) {
                return redirect()->route('asistencias.create', [
                    'section_id' => $bloque->section_id,
                    'subject_id' => $bloque->subject_id,
                ]);
            }
        }

        return redirect()->route('docente.cursos')
            ->with('mensaje', 'No tienes clases en este momento según tu horario. Elige el curso para llamar lista.');
    }

    /**
     * Formulario de registro diario para un curso.
     */
    public function create(Request $request): View
    {
        $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['nullable', 'integer'],
            'fecha' => ['nullable', 'date'],
        ]);

        $section = Section::with(['grade', 'gestion'])->findOrFail($request->query('section_id'));

        // El docente solo registra la asistencia del día actual; las demás fechas son para roles administrativos.
        $user = auth()->user();
        $fecha = $user->hasRole('Docente')
            ? now()->toDateString()
            : ($request->query('fecha') ? Carbon::parse($request->query('fecha'))->toDateString() : now()->toDateString());

        $this->authorizeDocenteDelCurso($section);

        $materias = $this->materiasDeCurso($section);
        $subjectId = $this->resolverMateriaOLaPrimera($request, $materias);

        $estudiantes = $this->estudiantesDelCurso($section);
        $asistenciasExistentes = Attendance::where('section_id', $section->id)
            ->whereDate('fecha', $fecha)
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId), fn ($q) => $q->whereNull('subject_id'))
            ->get()
            ->keyBy('student_id');

        $editar = $request->boolean('editar');

        // HU-82: estudiantes con licencia activa que cubre la fecha (no modificables)
        $licenciados = $this->estudiantesLicenciados($section, $fecha);

        return view('attendances.create', compact('section', 'fecha', 'estudiantes', 'asistenciasExistentes', 'editar', 'materias', 'subjectId', 'licenciados'));
    }

    /**
     * Guarda/actualiza la asistencia diaria en bloque.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['nullable', 'integer'],
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estado' => ['required', 'in:PRESENTE,AUSENTE,LICENCIA,RETRASO,RETIRO_JUSTIFICADO'],
            'asistencias.*.justificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $section = Section::findOrFail($validated['section_id']);
        $this->authorizeDocenteDelCurso($section);

        $estudiantes = $this->estudiantesDelCurso($section)->pluck('id')->toArray();

        // El docente solo puede guardar la asistencia del día actual.
        $user = auth()->user();
        $fechaNormalizada = $user->hasRole('Docente')
            ? now()->toDateString()
            : \Carbon\Carbon::parse($validated['fecha'])->toDateString();

        $materias = $this->materiasDeCurso($section);
        $subjectId = $this->resolverMateriaOLaPrimera($request, $materias);

        // HU-82: un estudiante con licencia activa no puede ser modificado por el docente.
        $licenciados = $this->estudiantesLicenciados($section, $fechaNormalizada);

        foreach ($validated['asistencias'] as $studentId => $data) {
            if (!in_array((int) $studentId, $estudiantes, true)) {
                continue;
            }

            if (in_array((int) $studentId, $licenciados, true)) {
                // Se preserva la licencia; se ignora cualquier estado enviado.
                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'section_id' => $section->id,
                        'fecha' => $fechaNormalizada,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'estado' => 'LICENCIA',
                        'registered_by_user_id' => auth()->id(),
                    ]
                );

                continue;
            }

            $existente = Attendance::where('student_id', $studentId)
                ->where('section_id', $section->id)
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
                    'section_id' => $section->id,
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
     * Asistencia de un curso del docente: vista por mes de cada estudiante,
     * ordenada por nombre (por defecto el mes actual).
     */
    public function porCurso(Request $request, SectionSubject $sectionSubject): View
    {
        $teacher = $request->user()->teacher;
        abort_if (! $teacher || $teacher->id !== $sectionSubject->teacher_id, 403);

        $sectionSubject->load(['subject', 'section.grade', 'section', 'section.gestion', 'schedules']);

        $section = $sectionSubject->section;

        $anio = $section->gestion?->fecha_inicio?->year ?? now()->year;
        $mesIni = $section->gestion?->fecha_inicio?->month ?? 2;
        $mesFin = $section->gestion?->fecha_fin?->month ?? 11;
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

        $students = $section->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $asistencias = Attendance::where('section_id', $section->id)
            ->where('subject_id', $sectionSubject->subject_id)
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

        $dias = $sectionSubject->schedules
            ->pluck('dia')
            ->unique()
            ->values();

        return view('attendances.por-curso', compact(
            'sectionSubject',
            'section',
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

    private function estudiantesDelCurso(Section $section)
    {
        // Estudiantes con matrícula ACTIVA en el curso
        return Student::whereHas('enrollments.registration', function ($q) use ($section) {
            $q->where('section_id', $section->id)->where('registrations.estado', 'ACTIVA');
        })->where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    /**
     * Materias disponibles de un curso según el rol: para el docente solo las que él imparte;
     * para roles administrativos todas las materias del curso.
     */
    private function materiasDeCurso(Section $section)
    {
        $user = auth()->user();
        $materias = SectionSubject::where('section_id', $section->id);

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

    /**
     * HU-82: ids de estudiantes del curso con licencia activa que cubre la fecha.
     */
    private function estudiantesLicenciados(Section $section, string $fecha): array
    {
        return Licencia::where('section_id', $section->id)
            ->where('estado', 'ACTIVA')
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_fin', '>=', $fecha)
            ->pluck('student_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function authorizeDocenteDelCurso(Section $section): void
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
            $esResponsable = SectionSubject::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->exists();

            // También permitir si es responsable del paralelo
            $esResponsableParalelo = \App\Models\Section::where('id', $section->id)
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
