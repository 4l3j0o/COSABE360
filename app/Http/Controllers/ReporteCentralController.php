<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Enrollment;
use App\Models\EvaluationPlan;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HU-58: Central de Reportes.
 *
 * Agrupa todos los reportes del sistema en un único módulo y añade los
 * reportes del "llamado a lista":
 *   - Lista de padres de familia (padres y apoderados)
 *   - Centralizador de calificaciones por evaluación (asignaturas)
 *   - Centralizador de calificaciones por trimestre (SE/SA/HA/AU)
 *
 * Permisos:
 *   - Administrador / Director / Secretaría: acceso total.
 *   - Docente: solo sus cursos/materias; el curso completo
 *     (centralizadores) solo si es tutor/encargado del paralelo
 *     (sections.teacher_id coincide con su Teacher).
 */
class ReporteCentralController extends Controller
{

    // ---------------------------------------------------------------- Index

    /**
     * Landing del módulo: listado de todos los reportes (nuevos y existentes)
     * con nombre, descripción, parámetros y botón Ejecutar.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);

        $reportes = $this->catalogoReportes($user);
        $gestiones = AcademicYear::orderByDesc('nombre')->get();

        return view('reportes-central.index', compact('cursos', 'sectionSubjects', 'reportes', 'gestiones'));
    }

    // -------------------------------------------- Lista de padres de familia

    public function padres(Request $request): View
    {
        abort_unless($request->user()->puede(['reportes.ver', 'reportes.administrativos.ver']), 403);

        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $gestiones = AcademicYear::orderByDesc('nombre')->get();
        $resultado = $this->buildPadres($request, $user, $cursos);

        return view('reportes-central.padres', [
            'cursos' => $cursos,
            'gestiones' => $gestiones,
            'filtros' => $request->only(['section_id', 'academic_year_id']),
            'resultado' => $resultado,
        ]);
    }

    public function exportPadres(Request $request)
    {
        abort_unless($request->user()->puede(['reportes.exportar', 'reportes.administrativos.ver']), 403);

        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildPadres($request, $user, $cursos);

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportPadresExcel($resultado);
        }

        // HU-81: dompdf no soporta miles de filas (400 → 174 MB/3 s,
        // 1500 → 950 MB/29 s). Con filtro por curso sí funciona.
        if ($resultado['filas']->count() > 500) {
            return redirect()->route('reportes.central.padres', $request->only(['section_id', 'academic_year_id']))
                ->with('error', 'El listado completo es muy grande para PDF (' . $resultado['filas']->count() . ' registros). Elija un curso o use el formato Excel.');
        }

        return $this->exportPdf('reportes-central.pdf.padres', $resultado, $this->nombreArchivo('lista-padres', $resultado, 'pdf'));
    }

    private function buildPadres(Request $request, $user, Collection $cursos): array
    {
        $sectionId = $request->query('section_id');
        $gestionId = $request->query('academic_year_id');

        if ($sectionId && ! $this->permiteCurso($user, $sectionId, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        $query = Student::with('parentGuardians')->where('students.estado', 'ACTIVO');

        if ($sectionId) {
            $section = Section::with(['grade', 'gestion'])->find($sectionId);
            $query->whereIn('students.id', $section->students()->pluck('students.id'));
        } elseif ($gestionId) {
            $ids = collect();
            foreach (Section::where('academic_year_id', $gestionId)->get() as $section) {
                $ids = $ids->merge($section->students()->pluck('students.id'));
            }
            $query->whereIn('students.id', $ids->unique());
        }

        $estudiantes = $query->orderBy('students.apellidos')->orderBy('students.nombres')->get();

        // HU-81: precarga los cursos activos en 3 consultas (antes: 2-4 por
        // estudiante vía activeSection(), ~13.000 con todos los cursos).
        $cursoDe = $this->cursosActivosDe($estudiantes->pluck('id'));

        $filas = collect();
        foreach ($estudiantes as $student) {
            if ($student->parentGuardians->isEmpty()) {
                $filas[] = [
                    'estudiante' => $student->apellidos.' '.$student->nombres,
                    'codigo' => $student->codigo,
                    'curso' => $cursoDe[$student->id] ?? '—',
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

            $reales = 0;
            foreach ($student->parentGuardians as $pg) {
                // HU-81: omitir filas sin datos reales (p. ej. direcciones
                // que quedaron como nombre con documento "S/N" y sin contacto).
                if (! $this->tutorConDatosReales($pg)) {
                    continue;
                }
                $reales++;
                $pivot = $pg->pivot;
                $filas[] = [
                    'estudiante' => $student->apellidos.' '.$student->nombres,
                    'codigo' => $student->codigo,
                    'curso' => $cursoDe[$student->id] ?? '—',
                    'relacion' => $pivot?->tipo_relacion ?? '—',
                    'apellidos' => $pg->apellidos ?? '',
                    'nombres' => $pg->nombres ?? '',
                    'ci' => trim(($pg->numero_documento ?? '').' '.($pg->complemento ?? '')),
                    'telefono' => $pg->telefono ?? '',
                    'email' => $pg->email ?? '',
                    'es_principal' => $pivot?->es_principal == 1,
                ];
            }
            if ($reales === 0) {
                // Tenía vínculos pero ninguno con datos reales.
                $filas[] = [
                    'estudiante' => $student->apellidos.' '.$student->nombres,
                    'codigo' => $student->codigo,
                    'curso' => $cursoDe[$student->id] ?? '—',
                    'relacion' => '—',
                    'apellidos' => '',
                    'nombres' => 'Sin tutor registrado',
                    'ci' => '',
                    'telefono' => '',
                    'email' => '',
                    'es_principal' => false,
                ];
            }
        }

        $curso = null;
        if ($sectionId) {
            $curso = Section::find($sectionId)?->codigoCorto();
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

    /**
     * HU-81: ¿la fila del tutor trae datos reales? Sin CI válido (5+ dígitos)
     * ni teléfono ni correo se considera basura (p. ej. una dirección que
     * quedó como nombre con documento "S/N").
     */
    private function tutorConDatosReales($pg): bool
    {
        if ((bool) preg_match('/\d{5,}/', (string) ($pg->numero_documento ?? ''))) {
            return true;
        }
        // Teléfono real: 6+ dígitos (descarta "S/N", "-", etc.).
        if (preg_match_all('/\d/', (string) ($pg->telefono ?? '')) >= 6) {
            return true;
        }
        if (filter_var($pg->email ?? '', FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    /**
     * HU-81: curso activo por estudiante en 3 consultas (misma lógica que
     * Student::activeRegistration: matrícula ACTIVA + inscripción COMPLETADA
     * de la gestión activa, tomando la más reciente).
     *
     * @return array<int, string> student_id => código corto del curso
     */
    private function cursosActivosDe(Collection $studentIds): array
    {
        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();
        if (! $periodo || $studentIds->isEmpty()) {
            return [];
        }

        $regs = Registration::with(['section.grade', 'section.gestion', 'enrollment'])
            ->where('registrations.estado', 'ACTIVA')
            ->whereHas('enrollment', fn ($q) => $q->where('academic_year_id', $periodo->id)
                ->where('enrollments.estado', 'COMPLETADA')
                ->whereIn('student_id', $studentIds))
            ->orderByDesc('id')
            ->get();

        $cursoDe = [];
        foreach ($regs as $r) {
            $sid = $r->enrollment->student_id;
            $cursoDe[$sid] ??= $r->section?->codigoCorto() ?? '—';
        }

        return $cursoDe;
    }

    // ------------------- Centralizador por evaluación (trimestres + anual)

    public function centralizadorEvaluacion(Request $request): View
    {
        abort_unless($request->user()->puede(['reportes.evaluacion.ver', 'reportes.ver']), 403);

        $user = $request->user();
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildCentralizadorEvaluacion($request, $user, $sectionSubjects);

        return view('reportes-central.centralizador-evaluacion', [
            'sectionSubjects' => $sectionSubjects,
            'cursos' => $cursos,
            'filtros' => $request->only(['section_subject_id', 'section_id', 'subject_id']),
            'resultado' => $resultado,
        ]);
    }

    public function exportCentralizadorEvaluacion(Request $request)
    {
        abort_unless($request->user()->puede(['reportes.exportar', 'reportes.evaluacion.ver']), 403);

        $user = $request->user();
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);
        $resultado = $this->buildCentralizadorEvaluacion($request, $user, $sectionSubjects);

        if ($resultado['requiere_curso']) {
            return back()->with('error', 'Selecciona el curso y la materia para generar el centralizador.');
        }

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportEvaluacionExcel($resultado);
        }

        return $this->exportPdf('reportes-central.pdf.centralizador-evaluacion', $resultado, $this->nombreArchivo('centralizador-evaluaciones', $resultado, 'pdf'));
    }

    private function buildCentralizadorEvaluacion(Request $request, $user, Collection $sectionSubjects): array
    {
        $cs = $this->resolverSectionSubject($request, $sectionSubjects);

        if (! $cs) {
            return [
                'curso' => null,
                'materia' => null,
                'trimestres' => [1, 2, 3],
                'filas' => collect(),
                'requiere_curso' => true,
            ];
        }

        $cs = SectionSubject::with(['subject', 'section.grade', 'section', 'section.gestion', 'teacher'])
            ->find($cs->id);

        if (! $cs) {
            return ['curso' => null, 'materia' => null, 'trimestres' => [1, 2, 3], 'filas' => collect(), 'requiere_curso' => true];
        }

        // Planes por trimestre del sistema vigente (evaluation_plans)
        $planes = [];
        foreach ([1, 2, 3] as $t) {
            $planes[$t] = EvaluationPlan::where('section_subject_id', $cs->id)
                ->where('trimestre', $t)
                ->with('criteria.activities')
                ->first();
        }

        $students = $cs->section->students()
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
            'curso' => $cs->section->codigoCorto(),
            'materia' => $cs->subject->nombre,
            'docente' => $cs->teacher ? trim(($cs->teacher->nombres ?? '') . ' ' . ($cs->teacher->apellidos ?? '')) : '—',
            'gestion' => $cs->section->gestion?->nombre ?? (string) now()->year,
            'trimestres' => [1, 2, 3],
            'filas' => $filas,
            'requiere_curso' => false,
        ];
    }

    // ------------------------------- Centralizador por trimestre (evaluation_plans)

    public function centralizadorTrimestre(Request $request): View
    {
        $user = $request->user();
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);
        $cursos = $this->cursosPermitidos($user);
        $resultado = $this->buildCentralizadorTrimestre($request, $user, $sectionSubjects);

        return view('reportes-central.centralizador-trimestre', [
            'sectionSubjects' => $sectionSubjects,
            'cursos' => $cursos,
            'filtros' => $request->only(['section_subject_id', 'section_id', 'subject_id', 'trimestre']),
            'resultado' => $resultado,
        ]);
    }

    public function exportCentralizadorTrimestre(Request $request)
    {
        $user = $request->user();
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);
        $resultado = $this->buildCentralizadorTrimestre($request, $user, $sectionSubjects);

        if ($resultado['requiere_curso']) {
            return back()->with('error', 'Selecciona el curso y la materia para generar el centralizador.');
        }

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportTrimestreExcel($resultado);
        }

        return $this->exportPdf('reportes-central.pdf.centralizador-trimestre', $resultado, $this->nombreArchivo('centralizador-trimestre', $resultado, 'pdf'));
    }

    private function buildCentralizadorTrimestre(Request $request, $user, Collection $sectionSubjects): array
    {
        $trimestre = (int) ($request->query('trimestre') ?: 1);
        $trimestre = in_array($trimestre, [1, 2, 3], true) ? $trimestre : 1;

        $cs = $this->resolverSectionSubject($request, $sectionSubjects);

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

        $cs = SectionSubject::with(['subject', 'section.grade', 'section', 'section.gestion', 'teacher'])
            ->find($cs->id);

        if (! $cs) {
            return ['curso' => null, 'materia' => null, 'plan' => null, 'filas' => collect(), 'trimestre' => $trimestre, 'requiere_curso' => true];
        }

        $plan = EvaluationPlan::where('section_subject_id', $cs->id)
            ->where('trimestre', $trimestre)
            ->with('criteria.activities')
            ->first();

        $students = $cs->section->students()
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
            'curso' => $cs->section->codigoCorto(),
            'materia' => $cs->subject->nombre,
            'docente' => $cs->teacher ? trim(($cs->teacher->nombres ?? '').' '.($cs->teacher->apellidos ?? '')) : '—',
            'gestion' => $cs->section->gestion?->nombre ?? (string) now()->year,
            'plan' => $plan,
            'filas' => $filas,
            'trimestre' => $trimestre,
            'requiere_curso' => false,
        ];
    }

    /**
     * Resuelve el section_subject desde section_subject_id (compat) o desde
     * el par curso + materia (filtros en cascada de la central).
     */
    private function resolverSectionSubject(Request $request, Collection $sectionSubjects): ?SectionSubject
    {
        if ($request->filled('section_subject_id')) {
            $cs = $sectionSubjects->firstWhere('id', (int) $request->query('section_subject_id'));
            abort_if(! $cs, 403, 'No tiene acceso a esa materia del curso.');

            return $cs;
        }

        if ($request->filled('section_id') && $request->filled('subject_id')) {
            $cs = $sectionSubjects
                ->first(fn ($c) => (int) $c->section_id === (int) $request->query('section_id')
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

    private function cursosPermitidos($user): Collection
    {
        if ($this->esSupervisor($user)) {
            return Section::with(['grade', 'gestion'])
                ->where('estado', 'ACTIVO')
                ->ordenadoPorNivel()
                ->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            // Secciones donde dicta + donde es tutor/encargado del paralelo.
            $propios = $user->teacher->sectionSubjects()
                ->with(['section.grade', 'section.gestion'])
                ->get()
                ->map(fn ($cs) => $cs->section);

            $encargado = Section::with(['grade', 'gestion'])
                ->where('estado', 'ACTIVO')
                ->where('teacher_id', $user->teacher->id)
                ->get();

            return $propios->concat($encargado)->unique('id')->values()->sortBy('nombre');
        }

        return collect();
    }

    private function sectionSubjectsPermitidos($user): Collection
    {
        if ($this->esSupervisor($user)) {
            return SectionSubject::with(['subject', 'section.grade', 'section', 'section.gestion'])
                ->whereHas('section', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->orderBy('section_id')
                ->orderBy('subject_id')
                ->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            // Sus materias + todas las materias de los cursos donde es
            // tutor/encargado (ve el curso completo).
            $cursosEncargado = Section::where('teacher_id', $user->teacher->id)
                ->pluck('id');

            return SectionSubject::with(['subject', 'section.grade', 'section', 'section.gestion'])
                ->whereHas('section', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->where(fn ($q) => $q
                    ->where('teacher_id', $user->teacher->id)
                    ->orWhereIn('section_id', $cursosEncargado))
                ->orderBy('section_id')
                ->orderBy('subject_id')
                ->get();
        }

        return collect();
    }

    private function permiteCurso($user, int $sectionId, Collection $cursos): bool
    {
        if ($this->esSupervisor($user)) {
            return true;
        }

        return $cursos->contains('id', $sectionId);
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
                'parametros' => 'rango / fecha / curso / materia',
                'ruta' => 'reportes.asistencia.index',
                'tipo' => 'link',
            ];
        }

        if ($esSupervisor || $esDocente) {
            $existentes[] = [
                'nombre' => 'Reportes de Evaluación',
                'descripcion' => 'Aprobados/reprobados, mejores calificaciones y asistencia por curso y trimestre.',
                'parametros' => 'curso / materia / trimestre',
                'ruta' => 'evaluation-reports.index',
                'tipo' => 'link',
            ];
        }

        // HU-66: Diario de Aula y Conducta viven dentro de la Central
        // (se retiran sus links directos de los sidebars).
        if ($user->hasAnyRole(['Administrador', 'Director', 'Docente', 'Estudiante'])) {
            $existentes[] = [
                'nombre' => 'Diario de Aula',
                'descripcion' => 'Bitácora de avances por clase: el docente registra, directivos y familias consultan.',
                'parametros' => 'curso / materia',
                'ruta' => 'diario.index',
                'tipo' => 'link',
            ];
        }

        if ($user->hasAnyRole(['Administrador', 'Director', 'Docente', 'Regente', 'Estudiante'])) {
            $existentes[] = [
                'nombre' => 'Conducta',
                'descripcion' => 'Observaciones de conducta por curso, materia y estudiante, con PDF/Excel.',
                'parametros' => 'curso / materia / estudiante / fechas',
                'ruta' => 'conducta.index',
                'tipo' => 'link',
            ];
        }

        if ($esSupervisor) {
            $existentes[] = [
                'nombre' => 'Cierre de Gestión',
                'descripcion' => 'Promedio anual, valoración oficial y promoción/retención por materia y curso.',
                'parametros' => 'curso',
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
                'descripcion' => 'Listado de inscripciones por gestión y estado. Ábralo y expórtelo si lo necesita.',
                'parametros' => 'gestión / estado',
                'ruta' => 'reportes.central.administrativo.inscripciones',
                'tipo' => 'link',
            ];
            // HU-58b: Matrículas y Pagos son financieros: sin Director
            // (también restringido en rutas).
            if ($user->hasAnyRole(['Administrador', 'Secretaría'])) {
                $administrativos[] = [
                    'nombre' => 'Matrículas',
                    'descripcion' => 'Estudiantes matriculados por curso y gestión. Ábralo y expórtelo si lo necesita.',
                    'parametros' => 'gestión / estado',
                    'ruta' => 'reportes.central.administrativo.matriculas',
                    'tipo' => 'link',
                ];
                $administrativos[] = [
                    'nombre' => 'Pagos y Deudas',
                    'descripcion' => 'Estado de pagos de mensualidades. Ábralo y expórtelo si lo necesita.',
                    'parametros' => 'gestión / estado',
                    'ruta' => 'reportes.central.administrativo.pagos',
                    'tipo' => 'link',
                ];
            }
            $administrativos[] = [
                'nombre' => 'Documentación Pendiente',
                'descripcion' => 'Documentos pendientes de entrega por estudiante. Ábralo y expórtelo si lo necesita.',
                'parametros' => 'estado',
                'ruta' => 'reportes.central.administrativo.documentacion',
                'tipo' => 'link',
            ];
        }

        return [
            'nuevos' => $nuevos,
            'existentes' => $existentes,
            'administrativos' => $administrativos,
        ];
    }

    // ------------------------------------ Administrativos con vista previa
    // HU-66b: primero Abrir (filtros + tabla en pantalla), luego exportar a PDF/Excel.

    public function verInscripciones(Request $request): View
    {
        abort_unless($request->user()->puede('reportes.administrativos.ver'), 403);

        $gestiones = AcademicYear::orderByDesc('nombre')->get();
        $filas = Enrollment::with(['estudiante', 'academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('id')->limit(500)->get()
            ->map(fn ($e) => [
                'estudiante' => $e->estudiante ? $e->estudiante->nombres . ' ' . $e->estudiante->apellidos : 'N/A',
                'codigo' => $e->estudiante?->codigo ?? '',
                'gestion' => $e->academicYear?->nombre ?? '',
                'fecha' => $e->fecha ?? '',
                'estado' => $e->estado,
            ]);

        return view('reportes-central.administrativo-inscripciones', [
            'gestiones' => $gestiones,
            'filtros' => $request->only(['academic_year_id', 'estado']),
            'filas' => $filas,
        ]);
    }

    public function verMatriculas(Request $request): View
    {
        abort_unless($request->user()->puede('reportes.administrativos.ver'), 403);

        $gestiones = AcademicYear::orderByDesc('nombre')->get();
        $filas = Registration::with(['enrollment.estudiante', 'section.grade', 'enrollment.academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('id')->limit(500)->get()
            ->map(fn ($m) => [
                'estudiante' => $m->enrollment?->estudiante ? $m->enrollment->estudiante->nombres . ' ' . $m->enrollment->estudiante->apellidos : 'N/A',
                'codigo' => $m->enrollment?->estudiante?->codigo ?? '',
                'curso' => $m->section?->codigoCorto() ?? '',
                'gestion' => $m->enrollment?->academicYear?->nombre ?? '',
                'estado' => $m->estado,
            ]);

        return view('reportes-central.administrativo-matriculas', [
            'gestiones' => $gestiones,
            'filtros' => $request->only(['academic_year_id', 'estado']),
            'filas' => $filas,
        ]);
    }

    public function verPagos(Request $request): View
    {
        abort_unless($request->user()->puede('reportes.administrativos.ver'), 403);

        $gestiones = AcademicYear::orderByDesc('nombre')->get();
        $mesNombre = [2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre'];
        $filas = MonthlyPayment::with(['registration.enrollment.estudiante', 'registration.section', 'registration.enrollment.academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('registration.enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('id')->limit(500)->get()
            ->map(fn ($p) => [
                'estudiante' => $p->registration?->enrollment?->estudiante ? $p->registration->enrollment->estudiante->nombres . ' ' . $p->registration->enrollment->estudiante->apellidos : 'N/A',
                'codigo' => $p->registration?->enrollment?->estudiante?->codigo ?? '',
                'mensualidad' => ($mesNombre[(int) $p->mes] ?? 'Mes ' . $p->mes) . ' ' . ($p->registration?->enrollment?->academicYear?->nombre ?? ''),
                'monto' => $p->monto ?? $p->monto_esperado,
                'estado' => $p->estado,
            ]);

        return view('reportes-central.administrativo-pagos', [
            'gestiones' => $gestiones,
            'filtros' => $request->only(['academic_year_id', 'estado']),
            'filas' => $filas,
        ]);
    }

    public function verDocumentacion(Request $request): View
    {
        abort_unless($request->user()->puede('reportes.administrativos.ver'), 403);

        $filas = StudentDocument::with(['student'])
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->when($request->tipo_documento, fn ($q, $v) => $q->where('tipo_documento', $v))
            ->orderByDesc('id')->limit(500)->get()
            ->map(fn ($d) => [
                'estudiante' => $d->student ? $d->student->nombres . ' ' . $d->student->apellidos : 'N/A',
                'codigo' => $d->student?->codigo ?? '',
                'tipo' => $d->tipo_documento,
                'estado' => $d->estado,
                'observaciones' => $d->observaciones ?? '',
            ]);

        return view('reportes-central.administrativo-documentacion', [
            'filtros' => $request->only(['estado', 'tipo_documento']),
            'filas' => $filas,
        ]);
    }

    // ------------------------------------------------------- Export PDF

    private function exportPdf(string $vista, array $resultado, string $filename): StreamedResponse
    {
        // HU-81: los listados grandes (p. ej. padres de todos los cursos)
        // agotan la memoria/tiempo por defecto de dompdf.
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $logoPath = public_path('images/escudo colegio.png');
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
        $this->escribirCabeceraExcel($sheet, $cabecera, 6);

        $fila = 7;
        $grupoEstudiante = 0;
        $codigoAnterior = null;
        foreach ($resultado['filas'] as $i => $f) {
            if ($f['codigo'] !== $codigoAnterior) {
                $grupoEstudiante++;
                $codigoAnterior = $f['codigo'];
            }

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

            if ($grupoEstudiante % 2 === 0) {
                $sheet->getStyle('A'.$fila.':K'.$fila)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF7F7F7');
            }

            $fila++;
        }

        $this->autoSizeExcel($sheet, $cabecera, $fila);
        $this->insertarLogoCentrado($sheet, 'A', 'B', 100, 2);

        return $this->streamXlsx($spreadsheet, $this->nombreArchivo('lista-padres', $resultado, 'xlsx'));
    }

    private function exportEvaluacionExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Centralizador');

        $titulo = 'Centralizador de Calificaciones por Evaluación (Trimestres + Anual)';
        $this->escribirMembreteExcel($sheet, $titulo, trim(($resultado['curso'] ?? '') . ' · ' . ($resultado['materia'] ?? '')), 6);

        $cabecera = ['N.º', 'Código', 'Estudiante', '1er Trim.', '2do Trim.', '3er Trim.', 'Promedio Anual', 'Valoración', 'Estado'];

        $this->escribirCabeceraExcel($sheet, $cabecera, 6);

        $fila = 7;
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
        $this->insertarLogoCentrado($sheet, 'A', 'B', 100, 2);

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
        // Altura para el escudo (logo de 100px en A1:B4).
        foreach ([1, 2, 3, 4] as $f) {
            $sheet->getRowDimension($f)->setRowHeight(16);
        }
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
        $sheet->getStyle('A6:'.$ultLetra.($filaFinal - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFD1DFD1');
        foreach (range(1, count($cabecera)) as $c) {
            $sheet->getColumnDimension($this->colLetter($c))->setAutoSize(true);
        }
        $sheet->freezePane('A7');
        $sheet->getSheetView()->setZoomScale(90);
    }

    private function streamXlsx(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        // HU-81: los listados grandes tardan en armarse celda por celda.
        set_time_limit(180);

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
        $path = public_path('images/escudo colegio.png');
        if (! is_file($path)) {
            return;
        }

        $size = @getimagesize($path);
        $ratio = ($size && $size[1] > 0) ? $size[0] / $size[1] : 1.975;
        $anchoPx = (int) round($alturaPx * $ratio);

        $bloquePx = 0;
        for ($c = Coordinate::columnIndexFromString($colIni); $c <= Coordinate::columnIndexFromString($colFin); $c++) {
            // Con autoSize el ancho aún no está calculado: usar el de defecto (8.43).
            $ancho = (float) $sheet->getColumnDimension($this->colLetter($c))->getWidth();
            if ($ancho <= 0) {
                $ancho = 8.43;
            }
            $bloquePx += (int) round($ancho * 7 + 5);
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
