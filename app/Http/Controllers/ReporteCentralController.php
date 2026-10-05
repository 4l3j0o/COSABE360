<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use App\Models\Student;
use App\Models\StudentRudeRecord;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HU-58: Central de Reportes.
 *
 * Agrupa todos los reportes del sistema en un único módulo y añade los
 * reportes del "llamado a lista":
 *   - Lista de padres de familia (padres y apoderados)
 *   - Lista de RUDEs del paralelo (curso completo solo si tutor/encargado)
 *   - Centralizador de calificaciones por evaluación (asignaturas)
 *   - Centralizador de calificaciones por trimestre (SE/SA/HA/AU)
 *
 * Permisos:
 *   - Administrador / Director / Secretaría: acceso total.
 *   - Docente: solo sus cursos/materias; el curso completo (RUDEs,
 *     centralizadores) solo si es tutor/encargado del paralelo
 *     (sections.teacher_id coincide con su Teacher).
 */
class ReporteCentralController extends Controller
{
    private const ESTADOS_RUDE = ['PENDIENTE', 'ENVIADO', 'APROBADO', 'RECHAZADO'];

    // ---------------------------------------------------------------- Index

    /**
     * Landing del módulo: listado de todos los reportes (nuevos y existentes)
     * con nombre, descripción, parámetros y botón Ejecutar.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $courseSubjects = $this->courseSubjectsPermitidos($user);

        $reportes = $this->catalogoReportes($user);
        $gestiones = AcademicYear::orderByDesc('nombre')->get();

        return view('reportes-central.index', compact('cursos', 'courseSubjects', 'reportes', 'gestiones'));
    }

    // -------------------------------------------- Lista de padres de familia

    public function padres(Request $request): View
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $gestiones = AcademicYear::orderByDesc('nombre')->get();
        $resultado = $this->buildPadres($request, $user, $cursos);

        return view('reportes-central.padres', [
            'cursos' => $cursos,
            'gestiones' => $gestiones,
            'filtros' => $request->only(['course_id', 'academic_year_id']),
            'resultado' => $resultado,
        ]);
    }

    public function exportPadres(Request $request)
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildPadres($request, $user, $cursos);

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportPadresExcel($resultado);
        }

        return $this->exportPdf('reportes-central.pdf.padres', $resultado, $this->nombreArchivo('lista-padres', $resultado, 'pdf'));
    }

    private function buildPadres(Request $request, $user, Collection $cursos): array
    {
        $courseId = $request->query('course_id');
        $gestionId = $request->query('academic_year_id');

        if ($courseId && ! $this->permiteCurso($user, $courseId, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        $query = Student::with('parentGuardians')->where('students.estado', 'ACTIVO');

        if ($courseId) {
            $course = Course::with(['grade', 'section', 'gestion'])->find($courseId);
            $query->whereIn('students.id', $course->students()->pluck('students.id'));
        } elseif ($gestionId) {
            $ids = collect();
            foreach (Course::where('academic_year_id', $gestionId)->get() as $course) {
                $ids = $ids->merge($course->students()->pluck('students.id'));
            }
            $query->whereIn('students.id', $ids->unique());
        }

        $estudiantes = $query->orderBy('students.apellidos')->orderBy('students.nombres')->get();

        $filas = collect();
        foreach ($estudiantes as $student) {
            if ($student->parentGuardians->isEmpty()) {
                $filas[] = [
                    'estudiante' => $student->apellidos.' '.$student->nombres,
                    'codigo' => $student->codigo,
                    'curso' => $student->activeCourse()?->codigoCorto() ?? '—',
                    'relacion' => '—',
                    'apellidos' => '',
                    'nombres' => 'Sin tutor registrado',
                    'ci' => '',
                    'telefono' => '',
                    'email' => '',
                    'es_principal' => false,
                ];
                continue;
            }

            foreach ($student->parentGuardians as $pg) {
                $pivot = $pg->pivot;
                $filas[] = [
                    'estudiante' => $student->apellidos.' '.$student->nombres,
                    'codigo' => $student->codigo,
                    'curso' => $student->activeCourse()?->codigoCorto() ?? '—',
                    'relacion' => $pivot?->tipo_relacion ?? '—',
                    'apellidos' => $pg->apellidos ?? '',
                    'nombres' => $pg->nombres ?? '',
                    'ci' => trim(($pg->numero_documento ?? '').' '.($pg->complemento ?? '')),
                    'telefono' => $pg->telefono ?? '',
                    'email' => $pg->email ?? '',
                    'es_principal' => $pivot?->es_principal == 1,
                ];
            }
        }

        $curso = null;
        if ($courseId) {
            $curso = Course::find($courseId)?->codigoCorto();
        } elseif ($gestionId) {
            $gestion = AcademicYear::find($gestionId);
            $curso = $gestion ? 'Gestión '.$gestion->nombre : null;
        }

        return [
            'curso' => $curso,
            'filas' => $filas,
            'requiere_curso' => false,
        ];
    }

    // ------------------------------------------------- Lista de RUDEs

    public function rudes(Request $request): View
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildRudes($request, $user, $cursos);

        return view('reportes-central.rudes', [
            'cursos' => $cursos,
            'filtros' => $request->only(['course_id']),
            'resultado' => $resultado,
        ]);
    }

    public function exportRudes(Request $request)
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildRudes($request, $user, $cursos);

        if ($resultado['requiere_curso']) {
            return back()->with('error', 'Selecciona un paralelo para generar la lista de RUDEs.');
        }

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportRudesExcel($resultado);
        }

        return $this->exportPdf('reportes-central.pdf.rudes', $resultado, $this->nombreArchivo('lista-rudes', $resultado, 'pdf'));
    }

    private function buildRudes(Request $request, $user, Collection $cursos): array
    {
        $courseId = $request->query('course_id');

        if (! $courseId) {
            return [
                'curso' => null,
                'filas' => collect(),
                'requiere_curso' => true,
            ];
        }

        if (! $this->permiteCurso($user, $courseId, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        // El curso completo (lista de RUDEs de todo el paralelo) solo si es
        // tutor/encargado del curso o rol de supervisión.
        $course = Course::with(['grade', 'section', 'gestion'])->find($courseId);
        if ($user->hasRole('Docente') && ! $this->esEncargadoCurso($user, $course)) {
            abort(403, 'Solo el tutor/encargado del curso puede ver la lista de RUDEs completa.');
        }

        $estudiantes = $course->students()
            ->with('rudeRecord')
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->orderBy('students.nombres')
            ->get();

        $filas = $estudiantes->map(fn ($student) => [
            'codigo' => $student->codigo,
            'apellidos' => $student->apellidos,
            'nombres' => $student->nombres,
            'ci' => trim(($student->numero_documento ?? '').' '.($student->complemento ?? '')),
            'rude' => $student->rudeRecord?->codigo_rude ?? 'POR GENERAR',
            'sie' => $student->rudeRecord?->codigo_sie_ue ?? StudentRudeRecord::SIE_UE,
            'estado' => $student->rudeRecord?->estado ?? 'PENDIENTE',
            'turno' => $student->rudeRecord?->turno ?? '',
        ]);

        return [
            'curso' => $course->codigoCorto(),
            'filas' => $filas,
            'requiere_curso' => false,
        ];
    }

    // ------------------- Centralizador por evaluación (trimestres + anual)

    public function centralizadorEvaluacion(Request $request): View
    {
        $user = $request->user();
        $courseSubjects = $this->courseSubjectsPermitidos($user);
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildCentralizadorEvaluacion($request, $user, $courseSubjects);

        return view('reportes-central.centralizador-evaluacion', [
            'courseSubjects' => $courseSubjects,
            'cursos' => $cursos,
            'filtros' => $request->only(['course_subject_id', 'course_id', 'subject_id']),
            'resultado' => $resultado,
        ]);
    }

    public function exportCentralizadorEvaluacion(Request $request)
    {
        $user = $request->user();
        $courseSubjects = $this->courseSubjectsPermitidos($user);
        $resultado = $this->buildCentralizadorEvaluacion($request, $user, $courseSubjects);

        if ($resultado['requiere_curso']) {
            return back()->with('error', 'Selecciona el curso y la materia para generar el centralizador.');
        }

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportEvaluacionExcel($resultado);
        }

        return $this->exportPdf('reportes-central.pdf.centralizador-evaluacion', $resultado, $this->nombreArchivo('centralizador-evaluaciones', $resultado, 'pdf'));
    }

    private function buildCentralizadorEvaluacion(Request $request, $user, Collection $courseSubjects): array
    {
        $cs = $this->resolverCourseSubject($request, $courseSubjects);

        if (! $cs) {
            return [
                'curso' => null,
                'materia' => null,
                'trimestres' => [1, 2, 3],
                'filas' => collect(),
                'requiere_curso' => true,
            ];
        }

        $cs = CourseSubject::with(['subject', 'course.grade', 'course.section', 'course.gestion', 'teacher'])
            ->find($cs->id);

        if (! $cs) {
            return ['curso' => null, 'materia' => null, 'trimestres' => [1, 2, 3], 'filas' => collect(), 'requiere_curso' => true];
        }

        // Planes por trimestre del sistema vigente (evaluation_plans)
        $planes = [];
        foreach ([1, 2, 3] as $t) {
            $planes[$t] = EvaluationPlan::where('course_subject_id', $cs->id)
                ->where('trimestre', $t)
                ->with('criteria.activities')
                ->first();
        }

        $students = $cs->course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->orderBy('students.nombres')
            ->get();

        $service = app(EvaluationService::class);

        $filas = $students->map(function ($student) use ($planes, $service, $cs) {
            $notas = [];
            foreach ([1, 2, 3] as $t) {
                $notas[$t] = $planes[$t] ? $service->notaTrimestre($planes[$t], $student) : null;
            }
            $anual = $service->promedioAnual($cs, $student);

            return [
                'codigo' => $student->codigo,
                'apellidos' => $student->apellidos,
                'nombres' => $student->nombres,
                'trimestres' => $notas,
                'anual' => $anual,
                'valoracion' => $anual !== null ? EvaluationService::valoracion($anual) : null,
                'aprobado' => $anual !== null ? EvaluationService::aprobado($anual) : null,
            ];
        });

        return [
            'curso' => $cs->course->codigoCorto(),
            'materia' => $cs->subject->nombre,
            'docente' => $cs->teacher ? trim(($cs->teacher->nombres ?? '') . ' ' . ($cs->teacher->apellidos ?? '')) : '—',
            'gestion' => $cs->course->gestion?->nombre ?? (string) now()->year,
            'trimestres' => [1, 2, 3],
            'filas' => $filas,
            'requiere_curso' => false,
        ];
    }

    // ------------------------------- Centralizador por trimestre (evaluation_plans)

    public function centralizadorTrimestre(Request $request): View
    {
        $user = $request->user();
        $courseSubjects = $this->courseSubjectsPermitidos($user);
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildCentralizadorTrimestre($request, $user, $courseSubjects);

        return view('reportes-central.centralizador-trimestre', [
            'courseSubjects' => $courseSubjects,
            'cursos' => $cursos,
            'filtros' => $request->only(['course_subject_id', 'course_id', 'subject_id', 'trimestre']),
            'resultado' => $resultado,
        ]);
    }

    public function exportCentralizadorTrimestre(Request $request)
    {
        $user = $request->user();
        $courseSubjects = $this->courseSubjectsPermitidos($user);
        $resultado = $this->buildCentralizadorTrimestre($request, $user, $courseSubjects);

        if ($resultado['requiere_curso']) {
            return back()->with('error', 'Selecciona el curso y la materia para generar el centralizador.');
        }

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportTrimestreExcel($resultado);
        }

        return $this->exportPdf('reportes-central.pdf.centralizador-trimestre', $resultado, $this->nombreArchivo('centralizador-trimestre', $resultado, 'pdf'));
    }

    private function buildCentralizadorTrimestre(Request $request, $user, Collection $courseSubjects): array
    {
        $trimestre = (int) ($request->query('trimestre') ?: 1);
        $trimestre = in_array($trimestre, [1, 2, 3], true) ? $trimestre : 1;

        $cs = $this->resolverCourseSubject($request, $courseSubjects);

        if (! $cs) {
            return [
                'curso' => null,
                'materia' => null,
                'plan' => null,
                'filas' => collect(),
                'trimestre' => $trimestre,
                'requiere_curso' => true,
            ];
        }

        $cs = CourseSubject::with(['subject', 'course.grade', 'course.section', 'course.gestion', 'teacher'])
            ->find($cs->id);

        if (! $cs) {
            return ['curso' => null, 'materia' => null, 'plan' => null, 'filas' => collect(), 'trimestre' => $trimestre, 'requiere_curso' => true];
        }

        $plan = EvaluationPlan::where('course_subject_id', $cs->id)
            ->where('trimestre', $trimestre)
            ->with('criteria.activities')
            ->first();

        $students = $cs->course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->orderBy('students.nombres')
            ->get();

        $service = app(EvaluationService::class);

        $filas = $students->map(function ($student) use ($service, $plan) {
            $criterios = [];
            $actividades = [];
            $completa = true;

            if ($plan) {
                foreach ($plan->criteria as $criterion) {
                    $nota = $service->notaCriterio($criterion, $student);
                    $criterios[$criterion->codigo] = $nota;
                    if ($nota === null) {
                        $completa = false;
                    }

                    $actividades[$criterion->id] = $criterion->activities->map(fn ($activity) => $activity->notaDe($student));
                }
            }

            $notaTrim = $plan && $completa ? $service->notaTrimestre($plan, $student) : null;

            return [
                'codigo' => $student->codigo,
                'apellidos' => $student->apellidos,
                'nombres' => $student->nombres,
                'criterios' => $criterios,
                'actividades' => $actividades,
                'notaTrim' => $notaTrim,
                'valoracion' => $notaTrim !== null ? EvaluationService::valoracion($notaTrim) : null,
                'aprobado' => $notaTrim !== null ? EvaluationService::aprobado($notaTrim) : null,
            ];
        });

        return [
            'curso' => $cs->course->codigoCorto(),
            'materia' => $cs->subject->nombre,
            'docente' => $cs->teacher ? trim(($cs->teacher->nombres ?? '').' '.($cs->teacher->apellidos ?? '')) : '—',
            'gestion' => $cs->course->gestion?->nombre ?? (string) now()->year,
            'plan' => $plan,
            'filas' => $filas,
            'trimestre' => $trimestre,
            'requiere_curso' => false,
        ];
    }

    /**
     * Resuelve el course_subject desde course_subject_id (compat) o desde
     * el par curso + materia (filtros en cascada de la central).
     */
    private function resolverCourseSubject(Request $request, Collection $courseSubjects): ?CourseSubject
    {
        if ($request->filled('course_subject_id')) {
            $cs = $courseSubjects->firstWhere('id', (int) $request->query('course_subject_id'));
            abort_if(! $cs, 403, 'No tiene acceso a esa materia del curso.');

            return $cs;
        }

        if ($request->filled('course_id') && $request->filled('subject_id')) {
            $cs = $courseSubjects
                ->first(fn ($c) => (int) $c->course_id === (int) $request->query('course_id')
                    && (int) $c->subject_id === (int) $request->query('subject_id'));
            abort_if(! $cs, 403, 'No tiene acceso a esa materia del curso.');

            return $cs;
        }

        return null;
    }

    // ------------------------------------------------------------ Permisos

    private function esSupervisor($user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Director', 'Secretaría']);
    }

    private function esEncargadoCurso($user, Course $course): bool
    {
        if (! $user->hasRole('Docente') || ! $user->teacher) {
            return false;
        }

        return $course->section?->teacher_id === $user->teacher->id;
    }

    private function cursosPermitidos($user): Collection
    {
        if ($this->esSupervisor($user)) {
            return Course::with(['grade', 'section', 'gestion'])
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            // Cursos donde dicta + cursos donde es tutor/encargado del paralelo.
            $propios = $user->teacher->courseSubjects()
                ->with(['course.grade', 'course.section', 'course.gestion'])
                ->get()
                ->map(fn ($cs) => $cs->course);

            $encargado = Course::with(['grade', 'section', 'gestion'])
                ->where('estado', 'ACTIVO')
                ->whereHas('section', fn ($q) => $q->where('teacher_id', $user->teacher->id))
                ->get();

            return $propios->concat($encargado)->unique('id')->values()->sortBy('nombre');
        }

        return collect();
    }

    private function courseSubjectsPermitidos($user): Collection
    {
        if ($this->esSupervisor($user)) {
            return CourseSubject::with(['subject', 'course.grade', 'course.section', 'course.gestion'])
                ->whereHas('course', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->orderBy('course_id')
                ->orderBy('subject_id')
                ->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            // Sus materias + todas las materias de los cursos donde es
            // tutor/encargado (ve el curso completo).
            $cursosEncargado = Course::whereHas('section', fn ($q) => $q->where('teacher_id', $user->teacher->id))
                ->pluck('id');

            return CourseSubject::with(['subject', 'course.grade', 'course.section', 'course.gestion'])
                ->whereHas('course', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->where(fn ($q) => $q
                    ->where('teacher_id', $user->teacher->id)
                    ->orWhereIn('course_id', $cursosEncargado))
                ->orderBy('course_id')
                ->orderBy('subject_id')
                ->get();
        }

        return collect();
    }

    private function permiteCurso($user, int $courseId, Collection $cursos): bool
    {
        if ($this->esSupervisor($user)) {
            return true;
        }

        return $cursos->contains('id', $courseId);
    }

    // ----------------------------------------------------------- Catálogo

    /**
     * Catálogo de reportes visible en la landing, según el rol.
     */
    private function catalogoReportes($user): array
    {
        $esDocente = $user->hasRole('Docente');
        $esSupervisor = $this->esSupervisor($user);

        $nuevos = [];

        if ($esSupervisor || $esDocente) {
            $nuevos[] = [
                'nombre' => 'Lista de Padres de Familia',
                'descripcion' => 'Muestra los padres y apoderados de cada estudiante con su parentesco, CI, teléfono y correo.',
                'parametros' => 'curso / gestión',
                'ruta' => 'reportes.central.padres',
                'tipo' => 'form',
            ];
        }

        if ($esSupervisor || $esDocente) {
            $nuevos[] = [
                'nombre' => 'Lista de RUDEs',
                'descripcion' => 'Lista de RUDEs del paralelo seleccionado. Solo el tutor/encargado del curso ve el curso completo.',
                'parametros' => 'paralelo (obligatorio)',
                'ruta' => 'reportes.central.rudes',
                'tipo' => 'form',
            ];
        }

        if ($esSupervisor || $esDocente) {
            $nuevos[] = [
                'nombre' => 'Centralizador de calificaciones por evaluación',
                'descripcion' => 'Notas de los 3 trimestres y promedio anual por materia y estudiante.',
                'parametros' => 'curso / materia',
                'ruta' => 'reportes.central.evaluacion',
                'tipo' => 'form',
            ];
        }

        if ($esSupervisor || $esDocente) {
            $nuevos[] = [
                'nombre' => 'Centralizador de calificaciones por trimestre',
                'descripcion' => 'Tabla de evaluaciones (SE/SA/HA/AU), materias y estudiantes por trimestre.',
                'parametros' => 'curso / materia / trimestre',
                'ruta' => 'reportes.central.trimestre',
                'tipo' => 'form',
            ];
        }

        $existentes = [];

        if ($esSupervisor || $esDocente) {
            $existentes[] = [
                'nombre' => 'Reportes de Asistencia',
                'descripcion' => 'Asistencia por día, semana, mes o trimestre con exportación PDF/Excel.',
                'parametros' => '—',
                'ruta' => 'reportes.asistencia.index',
                'tipo' => 'link',
            ];
        }

        if ($esSupervisor || $esDocente) {
            $existentes[] = [
                'nombre' => 'Reportes de Evaluación',
                'descripcion' => 'Aprobados/reprobados, mejores calificaciones y asistencia por curso y trimestre.',
                'parametros' => '—',
                'ruta' => 'evaluation-reports.index',
                'tipo' => 'link',
            ];
        }

        // HU-66: Diario de Aula y Conducta viven dentro de la Central
        // (se retiran sus links directos de los sidebars).
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Docente', 'Padre/Tutor', 'Estudiante'])) {
            $existentes[] = [
                'nombre' => 'Diario de Aula',
                'descripcion' => 'Bitácora de avances por clase: el docente registra, directivos y familias consultan.',
                'parametros' => '—',
                'ruta' => 'diario.index',
                'tipo' => 'link',
            ];
        }

        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría', 'Docente', 'Regente', 'Padre/Tutor', 'Estudiante'])) {
            $existentes[] = [
                'nombre' => 'Conducta',
                'descripcion' => 'Reportes de conducta (LEVE/GRAVE/MUY_GRAVE) por curso y por estudiante, con PDF/Excel.',
                'parametros' => '—',
                'ruta' => 'conducta.index',
                'tipo' => 'link',
            ];
        }

        if ($esSupervisor) {
            $existentes[] = [
                'nombre' => 'Cierre de Gestión',
                'descripcion' => 'Promedio anual, valoración oficial y promoción/retención por materia y curso.',
                'parametros' => '—',
                'ruta' => 'cierre-gestion.index',
                'tipo' => 'link',
            ];
        }

        // HU-58b: administrativos con descarga directa (absorben las páginas
        // viejas reportes/administrativos; los exports legacy académicos se
        // retiran por duplicar al centralizador con tablas vacías).
        $administrativos = [];

        if ($esSupervisor) {
            $administrativos[] = [
                'nombre' => 'Inscripciones',
                'descripcion' => 'Listado de inscripciones por gestión y estado.',
                'parametros' => 'gestión / estado',
                'ruta' => 'reportes.export.administrativo.inscripciones',
                'tipo' => 'download',
                'filtros' => ['gestion', 'estado_inscripcion'],
            ];
            // HU-58b: Matrículas y Pagos son financieros: sin Director
            // (también restringido en rutas).
            if ($user->hasAnyRole(['Administrador', 'Secretaría'])) {
                $administrativos[] = [
                    'nombre' => 'Matrículas',
                    'descripcion' => 'Estudiantes matriculados por curso y gestión.',
                    'parametros' => 'gestión / estado',
                    'ruta' => 'reportes.export.administrativo.matriculas',
                    'tipo' => 'download',
                    'filtros' => ['gestion', 'estado_matricula'],
                ];
                $administrativos[] = [
                    'nombre' => 'Pagos y Deudas',
                    'descripcion' => 'Estado de pagos de mensualidades.',
                    'parametros' => 'gestión / estado',
                    'ruta' => 'reportes.export.administrativo.pagos',
                    'tipo' => 'download',
                    'filtros' => ['gestion', 'estado_pago'],
                ];
            }
            $administrativos[] = [
                'nombre' => 'Documentación Pendiente',
                'descripcion' => 'Documentos pendientes de entrega por estudiante.',
                'parametros' => 'estado',
                'ruta' => 'reportes.export.administrativo.documentacion',
                'tipo' => 'download',
                'filtros' => ['estado_documento'],
            ];
        }

        return [
            'nuevos' => $nuevos,
            'existentes' => $existentes,
            'administrativos' => $administrativos,
        ];
    }

    // ------------------------------------------------------- Export PDF

    private function exportPdf(string $vista, array $resultado, string $filename): StreamedResponse
    {
        $logoPath = public_path('images/logo-colegio-thumb.png');
        $logo = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView($vista, compact('resultado', 'logo'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function nombreArchivo(string $base, array $resultado, string $extension): string
    {
        if ($resultado['curso']) {
            $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', (string) $resultado['curso']), '-'));
            if ($slug !== '') {
                $base .= '_'.$slug;
            }
        }

        return $base.'_'.now()->format('Ymd').'.'.$extension;
    }

    // ------------------------------------------------------- Export Excel

    private function exportPadresExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Padres');

        $this->escribirMembreteExcel($sheet, 'Lista de Padres de Familia — Padres y apoderados', $resultado['curso'] ?? 'Todos los cursos', 6);

        $cabecera = ['N.º', 'Código', 'Estudiante', 'Curso', 'Parentesco', 'Apellidos', 'Nombres', 'CI', 'Teléfono', 'Email', 'Principal'];
        $this->escribirCabeceraExcel($sheet, $cabecera, 2);

        $fila = 3;
        foreach ($resultado['filas'] as $i => $f) {
            $sheet->setCellValue('A'.$fila, $i + 1);
            $sheet->setCellValue('B'.$fila, $f['codigo']);
            $sheet->setCellValue('C'.$fila, $f['estudiante']);
            $sheet->setCellValue('D'.$fila, $f['curso']);
            $sheet->setCellValue('E'.$fila, $f['relacion']);
            $sheet->setCellValue('F'.$fila, $f['apellidos']);
            $sheet->setCellValue('G'.$fila, $f['nombres']);
            $sheet->setCellValue('H'.$fila, $f['ci']);
            $sheet->setCellValue('I'.$fila, $f['telefono']);
            $sheet->setCellValue('J'.$fila, $f['email']);
            $sheet->setCellValue('K'.$fila, $f['es_principal'] ? 'SI' : '');
            $fila++;
        }

        $this->autoSizeExcel($sheet, $cabecera, $fila);
        $this->insertarLogoCentrado($sheet, 'A', 'B', 84, 2);

        return $this->streamXlsx($spreadsheet, $this->nombreArchivo('lista-padres', $resultado, 'xlsx'));
    }

    private function exportRudesExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RUDEs');

        $this->escribirMembreteExcel($sheet, 'Lista de RUDEs del Paralelo', $resultado['curso'] ?? '', 6);

        $cabecera = ['N.º', 'Código', 'Apellidos', 'Nombres', 'CI', 'Código RUDE', 'SIE UE', 'Estado', 'Turno'];
        $this->escribirCabeceraExcel($sheet, $cabecera, 2);

        $fila = 3;
        foreach ($resultado['filas'] as $i => $f) {
            $sheet->setCellValue('A'.$fila, $i + 1);
            $sheet->setCellValue('B'.$fila, $f['codigo']);
            $sheet->setCellValue('C'.$fila, $f['apellidos']);
            $sheet->setCellValue('D'.$fila, $f['nombres']);
            $sheet->setCellValue('E'.$fila, $f['ci']);
            $sheet->setCellValue('F'.$fila, $f['rude']);
            $sheet->setCellValue('G'.$fila, $f['sie']);
            $sheet->setCellValue('H'.$fila, $f['estado']);
            $sheet->setCellValue('I'.$fila, $f['turno']);
            $fila++;
        }

        $this->autoSizeExcel($sheet, $cabecera, $fila);
        $this->insertarLogoCentrado($sheet, 'A', 'B', 84, 2);

        return $this->streamXlsx($spreadsheet, $this->nombreArchivo('lista-rudes', $resultado, 'xlsx'));
    }

    private function exportEvaluacionExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Centralizador');

        $titulo = 'Centralizador de Calificaciones por Evaluación (Trimestres + Anual)';
        $this->escribirMembreteExcel($sheet, $titulo, trim(($resultado['curso'] ?? '') . ' · ' . ($resultado['materia'] ?? '')), 6);

        $cabecera = ['N.º', 'Código', 'Estudiante', '1er Trim.', '2do Trim.', '3er Trim.', 'Promedio Anual', 'Valoración', 'Estado'];

        $this->escribirCabeceraExcel($sheet, $cabecera, 2);

        $fila = 3;
        foreach ($resultado['filas'] as $i => $f) {
            $sheet->setCellValue('A' . $fila, $i + 1);
            $sheet->setCellValue('B' . $fila, $f['codigo']);
            $sheet->setCellValue('C' . $fila, $f['apellidos'] . ' ' . $f['nombres']);
            $sheet->setCellValue('D' . $fila, $f['trimestres'][1] ?? '');
            $sheet->setCellValue('E' . $fila, $f['trimestres'][2] ?? '');
            $sheet->setCellValue('F' . $fila, $f['trimestres'][3] ?? '');
            $sheet->setCellValue('G' . $fila, $f['anual'] ?? '');
            $sheet->setCellValue('H' . $fila, $f['valoracion']['codigo'] ?? '—');
            $sheet->setCellValue('I' . $fila, $f['aprobado'] === null ? 'Sin nota' : ($f['aprobado'] ? 'Aprobado' : 'Reprobado'));
            $fila++;
        }

        $this->autoSizeExcel($sheet, $cabecera, $fila);
        $this->insertarLogoCentrado($sheet, 'A', 'B', 84, 2);

        return $this->streamXlsx($spreadsheet, $this->nombreArchivo('centralizador-evaluaciones', $resultado, 'xlsx'));
    }

    private function exportTrimestreExcel(array $resultado): StreamedResponse
    {
        // HU-58b: formato de planilla oficial (logo, tarjeta, bloques por
        // criterio) con valores calculados, vía EvaluationTemplateService.
        $contenido = app(\App\Services\EvaluationTemplateService::class)
            ->exportarCentralizador($resultado['plan']);

        return response()->streamDownload(
            fn () => print ($contenido),
            $this->nombreArchivo('centralizador-trimestre', $resultado, 'xlsx'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function escribirMembreteExcel($sheet, string $titulo, string $subtitulo, int $filasMembrete): void
    {
        $ultCol = $this->colLetter(13);
        $sheet->setCellValue('C1', 'UNIDAD EDUCATIVA SAN BERNARDO — TARIJA');
        $sheet->mergeCells('C1:'.$ultCol.'1');
        $sheet->setCellValue('C2', $titulo);
        $sheet->mergeCells('C2:'.$ultCol.'2');
        $sheet->setCellValue('C3', $subtitulo);
        $sheet->mergeCells('C3:'.$ultCol.'3');
        $sheet->setCellValue('C4', 'Generado: '.now()->format('d/m/Y H:i'));
        $sheet->mergeCells('C4:'.$ultCol.'4');

        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF003200');
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF003200');
        $sheet->getStyle('C3:C4')->getFont()->setSize(10)->getColor()->setARGB('FF33412F');
    }

    private function escribirCabeceraExcel($sheet, array $cabecera, int $filaCabecera): void
    {
        $ultLetra = $this->colLetter(count($cabecera));
        foreach ($cabecera as $i => $texto) {
            $sheet->setCellValue($this->colLetter($i + 1).$filaCabecera, $texto);
        }
        $rango = 'A'.$filaCabecera.':'.$ultLetra.$filaCabecera;
        $sheet->getStyle($rango)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rango)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF003200');
        $sheet->getStyle($rango)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($filaCabecera)->setRowHeight(24);
    }

    private function autoSizeExcel($sheet, array $cabecera, int $filaFinal): void
    {
        $ultLetra = $this->colLetter(count($cabecera));
        $sheet->getStyle('A2:'.$ultLetra.($filaFinal - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFD1DFD1');
        foreach (range(1, count($cabecera)) as $c) {
            $sheet->getColumnDimension($this->colLetter($c))->setAutoSize(true);
        }
        $sheet->freezePane('A3');
        $sheet->getSheetView()->setZoomScale(90);
    }

    private function streamXlsx(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $contenido = (string) ob_get_clean();

        return response()->streamDownload(
            fn () => print ($contenido),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function colLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }

    /**
     * Inserta el logo institucional centrado horizontalmente en un bloque de columnas.
     */
    private function insertarLogoCentrado($sheet, string $colIni, string $colFin, int $alturaPx, int $offsetY = 0): void
    {
        $path = public_path('images/logo-colegio-thumb.png');
        if (! is_file($path)) {
            return;
        }

        $size = @getimagesize($path);
        $ratio = ($size && $size[1] > 0) ? $size[0] / $size[1] : 1.975;
        $anchoPx = (int) round($alturaPx * $ratio);

        $bloquePx = 0;
        for ($c = Coordinate::columnIndexFromString($colIni); $c <= Coordinate::columnIndexFromString($colFin); $c++) {
            $bloquePx += (int) round($sheet->getColumnDimension($this->colLetter($c))->getWidth() * 7 + 5);
        }

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
        $drawing->setPath($path);
        $drawing->setCoordinates($colIni.'1');
        $drawing->setResizeProportional(true);
        $drawing->setHeight($alturaPx);
        $drawing->setOffsetX(max(0, (int) round(($bloquePx - $anchoPx) / 2)));
        $drawing->setOffsetY($offsetY);
        $drawing->setWorksheet($sheet);
    }
}