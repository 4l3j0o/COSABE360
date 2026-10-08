<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConductaReporteRequest;
use App\Models\ConductaReporte;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConductaReporteController extends Controller
{
    /**
     * HU-65: reportes de conducta — el docente reporta faltas
     * LEVE/GRAVE/MUY_GRAVE; curso y estudiante generan reportes.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);

        if (! $request->query('course_id') && $cursos->count() === 1) {
            $request->merge(['course_id' => $cursos->first()->id]);
        }

        $resultado = $this->build($request, $user, $cursos, null);

        return view('conducta.index', [
            'cursos' => $cursos,
            'materias' => $resultado['materias'],
            'resultado' => $resultado,
            'filtros' => $request->only(['course_id', 'subject_id', 'tipo_falta', 'desde', 'hasta']),
        ]);
    }

    /**
     * HU-65c: acción rápida — abre la conducta de la clase actual según horario;
     * fuera de horario avisa y lleva a Mis Cursos.
     */
    public function ahora(): RedirectResponse
    {
        $bloque = $this->bloqueActual();

        if (! $bloque) {
            return redirect()->route('docente.cursos')
                ->with('mensaje', 'Fuera de tu horario de clases. Elige el curso para abrir su conducta.');
        }

        return redirect()->route('conducta.index', [
            'course_id' => $bloque->course_id,
            'subject_id' => $bloque->subject_id,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeRegistro($request->user());

        $courseId = $request->query('course_id');
        $cursos = $this->cursosRegistro($request->user());
        $course = $courseId
            ? $cursos->firstWhere('id', (int) $courseId)
            : ($cursos->count() === 1 ? $cursos->first() : null);

        $materias = $course ? $this->materiasRegistro($request->user(), $course) : collect();
        $estudiantes = $course ? $this->estudiantesDelCurso($course) : collect();

        return view('conducta.create', [
            'cursos' => $cursos,
            'course' => $course,
            'materias' => $materias,
            'estudiantes' => $estudiantes,
            'tipos' => ConductaReporte::TIPOS_LABEL,
            'hoy' => now()->toDateString(),
        ]);
    }

    public function store(StoreConductaReporteRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeRegistro($user);

        $data = $request->validated();
        $course = Course::findOrFail($data['course_id']);
        $this->authorizeDocenteDelCurso($user, $course);
        abort_unless($this->estudianteEnCurso((int) $data['student_id'], $course->id), 403, 'El estudiante no pertenece a ese curso.');
        if (! empty($data['subject_id'])) {
            $this->authorizeMateria($user, $course, (int) $data['subject_id']);
        }

        ConductaReporte::create($data + [
            'teacher_id' => $this->teacherIdPara($user, $course, $data['subject_id'] ?? null),
            // HU-65d: si no se indica la hora, se registra la hora local automáticamente
            'hora' => $data['hora'] ?? now('America/La_Paz')->format('H:i'),
            'registered_by_user_id' => $user->id,
        ]);

        return redirect()->route('conducta.index', ['course_id' => $course->id])
            ->with('mensaje', 'Reporte de conducta registrado.');
    }

    public function show(Request $request, ConductaReporte $conducta): View
    {
        $user = $request->user();
        $this->authorizeVerReporte($user, $conducta);
        $conducta->load(['student', 'course.grade', 'course.section', 'subject', 'teacher']);

        return view('conducta.show', ['reporte' => $conducta]);
    }

    /**
     * Reporte específico de un estudiante: historial + conteo por tipo.
     */
    public function estudiante(Request $request): View
    {
        $user = $request->user();
        $estudiantes = $this->estudiantesVisibles($user);
        $studentId = $request->query('student_id') ?: ($estudiantes->count() === 1 ? $estudiantes->first()->id : null);

        $student = $studentId ? $estudiantes->firstWhere('id', (int) $studentId) : null;
        abort_if($request->query('student_id') && ! $student, 403, 'No tiene acceso a ese estudiante.');

        $reportes = collect();
        $conteo = ['LEVE' => 0, 'GRAVE' => 0, 'MUY_GRAVE' => 0];
        if ($student) {
            $reportes = ConductaReporte::with(['course.grade', 'course.section', 'subject', 'teacher'])
                ->where('student_id', $student->id)
                ->orderByDesc('fecha')->orderByDesc('id')->get();
            foreach ($reportes as $r) {
                $conteo[$r->tipo_falta] = ($conteo[$r->tipo_falta] ?? 0) + 1;
            }
        }

        return view('conducta.estudiante', [
            'estudiantes' => $estudiantes,
            'student' => $student,
            'reportes' => $reportes,
            'conteo' => $conteo,
            'filtros' => $request->only(['student_id']),
        ]);
    }

    /**
     * Exporta el reporte general del curso o el específico del estudiante.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $formato = $request->query('format', 'pdf');

        if ($request->query('student_id')) {
            $estudiantes = $this->estudiantesVisibles($user);
            $student = $estudiantes->firstWhere('id', (int) $request->query('student_id'));
            abort_unless($student, 403, 'No tiene acceso a ese estudiante.');
            $filas = $this->filasEstudiante($student);
            $titulo = 'Reporte de Conducta - '.$student->apellidos.' '.$student->nombres;
            $nombre = 'conducta-'.$student->codigo;
        } else {
            $resultado = $this->build($request, $user, $cursos, null);
            abort_if($resultado['requiere_curso'], 400, 'Seleccione un curso para exportar.');
            $filas = $resultado['filas'];
            $titulo = 'Reporte de Conducta - '.($resultado['curso'] ?? 'General');
            $nombre = 'conducta-curso';
        }

        return $formato === 'excel'
            ? $this->exportExcel($titulo, $nombre, $filas)
            : $this->exportPdf($titulo, $nombre, $filas);
    }

    // ---------- construcción del reporte general ----------

    private function build(Request $request, $user, $cursos, $soloStudentId): array
    {
        $courseId = $request->query('course_id');
        $subjectId = $request->query('subject_id');
        $tipo = $request->query('tipo_falta');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $course = $courseId ? Course::with(['grade', 'section', 'gestion'])->find($courseId) : null;
        if ($course && ! $this->permiteCurso($user, $course->id, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        $materias = $this->materiasVisibles($user, $courseId, $cursos);
        if ($subjectId && ! $materias->contains('id', (int) $subjectId)) {
            $subjectId = null;
        }

        $requiereCurso = $this->requiereCurso($user);
        $filas = collect();
        if (! $requiereCurso || $courseId) {
            $q = ConductaReporte::with(['student', 'course.grade', 'course.section', 'subject', 'teacher'])
                ->when($courseId, fn ($qq) => $qq->where('course_id', $courseId),
                    fn ($qq) => $qq->whereIn('course_id', $cursos->pluck('id')))
                ->when($subjectId, fn ($qq) => $qq->where('subject_id', $subjectId))
                ->when($tipo, fn ($qq) => $qq->where('tipo_falta', $tipo))
                ->when($desde, fn ($qq) => $qq->where('fecha', '>=', $desde))
                ->when($hasta, fn ($qq) => $qq->where('fecha', '<=', $hasta))
                ->when($soloStudentId, fn ($qq) => $qq->where('student_id', $soloStudentId));

            // El estudiante solo ve sus propios reportes
            if ($user->hasRole('Estudiante') && $user->student) {
                $q->where('student_id', $user->student->id);
            }

            $filas = $q->orderByDesc('fecha')->orderByDesc('id')->get();
        }

        $materiaNombre = $subjectId
            ? (Subject::find($subjectId)->nombre ?? null)
            : null;

        return [
            'curso' => $course?->nombre,
            'materia' => $materiaNombre,
            'tipo' => $tipo ? (ConductaReporte::TIPOS_LABEL[$tipo] ?? $tipo) : null,
            'desde' => $desde,
            'hasta' => $hasta,
            'filas' => $filas,
            'materias' => $materias,
            'requiere_curso' => $requiereCurso && ! $courseId,
            'conteo' => $filas->groupBy('tipo_falta')->map->count(),
        ];
    }

    private function filasEstudiante(Student $student)
    {
        return ConductaReporte::with(['student', 'course.grade', 'course.section', 'subject', 'teacher'])
            ->where('student_id', $student->id)
            ->orderByDesc('fecha')->orderByDesc('id')->get();
    }

    private function exportPdf(string $titulo, string $nombre, $filas)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('conducta.pdf', compact('titulo', 'filas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($nombre.'.pdf');
    }

    private function exportExcel(string $titulo, string $nombre, $filas): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Conducta');
        $sheet->setCellValue('A1', $titulo);

        $cabecera = ['N.º', 'Fecha', 'Hora', 'Estudiante', 'Curso', 'Materia', 'Tipo de falta', 'Descripción', 'Reportado por'];
        $filaCabecera = 3;
        foreach ($cabecera as $i => $texto) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$filaCabecera, $texto);
        }

        $fila = $filaCabecera + 1;
        foreach ($filas as $i => $r) {
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $i + 1);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->fecha->format('d/m/Y'));
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->hora ? substr($r->hora, 0, 5) : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->student->apellidos.' '.$r->student->nombres);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->course->nombre ?? '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->subject->nombre ?? 'General');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->tipoLabel());
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->descripcion);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->teacher ? $r->teacher->nombres.' '.$r->teacher->apellidos : '-');
            $fila++;
        }

        $ultima = Coordinate::stringFromColumnIndex(count($cabecera));
        $sheet->getStyle('A'.$filaCabecera.':'.$ultima.$filaCabecera)->getFont()->setBold(true);
        foreach (range(1, count($cabecera)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $nombre.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
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
            403, 'Solo docentes y personal administrativo reportan conducta.'
        );
    }

    private function authorizeDocenteDelCurso($user, Course $course): void
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return;
        }
        if ($user->hasRole('Docente') && $user->teacher) {
            $asignado = CourseSubject::where('teacher_id', $user->teacher->id)
                ->where('course_id', $course->id)->exists()
                || \App\Models\Section::where('id', $course->section_id)
                    ->where('teacher_id', $user->teacher->id)->exists();
            if ($asignado) {
                return;
            }
        }
        abort(403, 'No está asignado a este curso.');
    }

    private function authorizeMateria($user, Course $course, int $subjectId): void
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return;
        }
        $ok = CourseSubject::where('course_id', $course->id)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $user->teacher->id)
            ->exists()
            || \App\Models\Section::where('id', $course->section_id)
                ->where('teacher_id', $user->teacher->id)->exists();
        abort_unless($ok, 403, 'Esa materia no le corresponde en este curso.');
    }

    private function authorizeVerReporte($user, ConductaReporte $reporte): void
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Regente'])) {
            return;
        }
        if ($user->hasRole('Docente') && $user->teacher) {
            $asignado = CourseSubject::where('teacher_id', $user->teacher->id)
                ->where('course_id', $reporte->course_id)->exists();
            abort_unless($asignado, 403, 'No tiene acceso a este reporte.');
            return;
        }
        if ($user->hasRole('Estudiante') && $user->student) {
            abort_unless($reporte->student_id === $user->student->id, 403);
            return;
        }
        if ($user->hasRole('Padre/Tutor')) {
            $hijos = $this->hijosIds($user);
            abort_unless($hijos->contains($reporte->student_id), 403);
            return;
        }
        abort(403);
    }

    private function estudianteEnCurso(int $studentId, int $courseId): bool
    {
        return Registration::where('estado', 'ACTIVA')->where('course_id', $courseId)
            ->whereHas('enrollment', fn ($q) => $q->where('student_id', $studentId)->where('estado', 'COMPLETADA'))
            ->exists();
    }

    private function estudiantesDelCurso(Course $course)
    {
        return Registration::where('estado', 'ACTIVA')->where('course_id', $course->id)
            ->with('enrollment.estudiante')
            ->whereHas('enrollment.estudiante', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->get()->map(fn ($r) => $r->enrollment?->estudiante)->filter()->unique('id')->values()
            ->sortBy(fn ($e) => $e->apellidos.' '.$e->nombres)->values();
    }

    private function estudiantesVisibles($user)
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Regente'])) {
            return Student::where('estado', 'ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get();
        }
        if ($user->hasRole('Docente') && $user->teacher) {
            $courseIds = CourseSubject::where('teacher_id', $user->teacher->id)->pluck('course_id')->unique();
            return Registration::where('estado', 'ACTIVA')->whereIn('course_id', $courseIds)
                ->with('enrollment.estudiante')
                ->whereHas('enrollment.estudiante', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->get()->map(fn ($r) => $r->enrollment?->estudiante)->filter()->unique('id')->values()
                ->sortBy(fn ($e) => $e->apellidos.' '.$e->nombres)->values();
        }
        if ($user->hasRole('Estudiante') && $user->student) {
            return collect([$user->student]);
        }
        if ($user->hasRole('Padre/Tutor')) {
            $guardian = ParentGuardian::where('user_id', $user->id)->first();

            return $guardian ? $guardian->students()->orderBy('apellidos')->get() : collect();
        }

        return collect();
    }

    private function hijosIds($user)
    {
        $guardian = ParentGuardian::where('user_id', $user->id)->first();

        return $guardian ? $guardian->students()->pluck('students.id') : collect();
    }

    private function teacherIdPara($user, Course $course, $subjectId): ?int
    {
        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->id;
        }

        return $subjectId
            ? CourseSubject::where('course_id', $course->id)->where('subject_id', $subjectId)->value('teacher_id')
            : CourseSubject::where('course_id', $course->id)->value('teacher_id');
    }

    private function cursosPermitidos($user)
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Regente'])) {
            return Course::with(['grade', 'section', 'gestion'])->ordenadoPorNivel()->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->courseSubjects()
                ->with(['course.grade', 'course.section', 'course.gestion'])
                ->get()->map(fn ($cs) => $cs->course)->unique('id')->pipe(fn ($c) => Course::ordenarPorNivel($c));
        }

        if ($user->hasRole('Estudiante') && $user->student) {
            $curso = $user->student->activeCourse();

            return $curso ? collect([$curso]) : collect();
        }

        if ($user->hasRole('Padre/Tutor')) {
            $courseIds = Registration::where('estado', 'ACTIVA')
                ->whereHas('enrollment', fn ($q) => $q->whereIn('student_id', $this->hijosIds($user))->where('estado', 'COMPLETADA'))
                ->pluck('course_id')->unique()->values();

            return Course::with(['grade', 'section', 'gestion'])->whereIn('id', $courseIds)->ordenadoPorNivel()->get();
        }

        return collect();
    }

    private function cursosRegistro($user)
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return Course::with(['grade', 'section'])->where('estado', 'ACTIVO')->ordenadoPorNivel()->get();
        }

        return $this->cursosPermitidos($user)->where('estado', 'ACTIVO')->values();
    }

    private function materiasVisibles($user, $courseId, $cursos)
    {
        $q = CourseSubject::with('subject');
        if ($courseId) {
            $q->where('course_id', $courseId);
        } else {
            $q->whereIn('course_id', $cursos->pluck('id'));
        }
        if ($user->hasRole('Docente') && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        }

        return $q->get()->map(fn ($cs) => $cs->subject)->unique('id')->values();
    }

    private function materiasRegistro($user, Course $course)
    {
        $q = CourseSubject::where('course_id', $course->id);
        if ($user->hasRole('Docente') && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        }
        $ids = $q->pluck('subject_id')->unique()->values();

        return $ids->isEmpty() ? collect() : Subject::whereIn('id', $ids)->orderBy('nombre')->get();
    }

    private function requiereCurso($user): bool
    {
        return $user->hasRole('Docente') || $user->hasRole('Estudiante') || $user->hasRole('Padre/Tutor');
    }

    private function permiteCurso($user, int $courseId, $cursos): bool
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Regente'])) {
            return true;
        }

        return $cursos->contains('id', $courseId);
    }
}
