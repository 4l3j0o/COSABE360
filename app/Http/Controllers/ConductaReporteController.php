<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConductaReporteRequest;
use App\Models\ConductaReporte;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ConductaScopeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HU-82: Conducta — observaciones de conducta por estudiante.
 *
 * Vistas:
 *  - Docente: "Mis reportes" (solo los que creó) y "Mi paralelo" (si es tutor/encargado).
 *  - Regente / Administrador: "Conducta general" (todos).
 *  - Director: "Conducta general" limitada a su nivel.
 *  - Estudiante: solo su historial.
 *  - Secretaría: sin acceso.
 */
class ConductaReporteController extends Controller
{
    public function __construct(private readonly ConductaScopeService $scope) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->scope->tieneAcceso($user), 403, 'No tiene acceso al módulo de conducta.');

        $sectionId = $request->filled('section_id') ? (int) $request->query('section_id') : null;
        $subjectId = $request->filled('subject_id') ? (int) $request->query('subject_id') : null;
        $contexto = $request->boolean('contexto') && $sectionId !== null;
        $vista = $this->scope->resolverVista($user, $request->query('vista'), $sectionId, $subjectId);
        $cursos = $this->scope->cursosVisibles($user, $vista);

        if ($sectionId && ! $cursos->pluck('id')->contains($sectionId)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        $materias = $this->scope->materiasVisibles($user, $vista, $sectionId);
        $estudiantes = $this->scope->estudiantesVisibles($user, $vista, $sectionId);

        $filtros = [
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'student_id' => $request->filled('student_id') ? (int) $request->query('student_id') : null,
            'desde' => $request->query('desde'),
            'hasta' => $request->query('hasta'),
        ];

        if ($filtros['subject_id'] && ! $materias->contains('id', $filtros['subject_id'])) {
            abort(403, 'No tiene acceso a esa materia.');
        }

        if ($filtros['student_id'] && ! $estudiantes->contains('id', $filtros['student_id'])) {
            abort(403, 'No tiene acceso a ese estudiante.');
        }

        $q = $this->scope->query($user, $vista, $filtros);
        $resumen = $this->resumen($q);
        $filas = $q->paginate(30)->withQueryString();

        $tabs = $this->tabs($user);
        $sectionActual = $sectionId ? $cursos->firstWhere('id', $sectionId) : null;
        $materiaActual = $subjectId ? $materias->firstWhere('id', $subjectId) : null;

        return view('conducta.index', [
            'vista' => $vista,
            'tabs' => $tabs,
            'cursos' => $cursos,
            'materias' => $materias,
            'estudiantes' => $estudiantes,
            'filtros' => $filtros,
            'filas' => $filas,
            'resumen' => $resumen,
            'contextoCurso' => $contexto,
            'sectionActual' => $sectionActual,
            'materiaActual' => $materiaActual,
            'descripcion' => $this->descripcionVista($vista, $user),
            'mostrarAutor' => in_array($vista, ['paralelo', 'general'], true),
            'puedeCrear' => ! $this->scope->esEstudiante($user),
        ]);
    }

    /** Acción rápida: abre la conducta del bloque de horario actual. */
    public function ahora(): RedirectResponse
    {
        $bloque = $this->bloqueActual();

        if (! $bloque) {
            return redirect()->route('docente.cursos')
                ->with('mensaje', 'Fuera de tu horario de clases. Elige el curso para abrir su conducta.');
        }

        return redirect()->route('conducta.index', [
            'vista' => 'mios',
            'section_id' => $bloque->section_id,
            'subject_id' => $bloque->subject_id,
            'contexto' => 1,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless(! $this->scope->esEstudiante($user), 403, 'No puede registrar reportes de conducta.');

        $cursos = $this->scope->cursosCreables($user);
        abort_if($cursos->isEmpty(), 403, 'No tiene cursos asignados para registrar conducta.');

        $sectionId = $request->query('section_id');
        $section = $sectionId
            ? $cursos->firstWhere('id', (int) $sectionId)
            : ($cursos->count() === 1 ? $cursos->first() : null);

        abort_if($sectionId && ! $section, 403, 'No tiene acceso a ese curso.');

        $materias = $section ? $this->scope->materiasCreables($user, $section) : collect();

        $subjectId = $request->filled('subject_id') ? (int) $request->query('subject_id') : null;
        if ($subjectId && ! $materias->contains('id', $subjectId)) {
            abort(403, 'No tiene acceso a esa materia.');
        }

        $estudiantes = $section ? $this->scope->estudiantesDelCurso($section) : collect();

        return view('conducta.create', [
            'cursos' => $cursos,
            'section' => $section,
            'materias' => $materias,
            'estudiantes' => $estudiantes,
            'subjectId' => $subjectId,
            'contexto' => $request->boolean('contexto'),
            'vista' => $this->scope->resolverVista($user, $request->query('vista'), $section?->id, $subjectId),
            'hoy' => now()->toDateString(),
        ]);
    }

    public function store(StoreConductaReporteRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(! $this->scope->esEstudiante($user), 403, 'No puede registrar reportes de conducta.');

        $data = $request->validated();
        $section = Section::findOrFail($data['section_id']);
        $subjectId = ! empty($data['subject_id']) ? (int) $data['subject_id'] : null;

        abort_unless($this->scope->puedeCrear($user, $section, $subjectId), 403, 'No está asignado a este curso.');

        $estudiantes = $this->scope->estudiantesDelCurso($section);
        abort_unless($estudiantes->contains('id', (int) $data['student_id']), 403, 'El estudiante no pertenece a ese curso.');

        ConductaReporte::create([
            'student_id' => $data['student_id'],
            'section_id' => $section->id,
            'subject_id' => $subjectId,
            'teacher_id' => $this->scope->teacherIdPara($user, $section, $subjectId),
            'fecha' => $data['fecha'],
            // Si no se indica la hora, se registra la hora local automáticamente.
            'hora' => $data['hora'] ?? now('America/La_Paz')->format('H:i'),
            'descripcion' => $data['descripcion'],
            'registered_by_user_id' => $user->id,
        ]);

        $vista = $this->scope->resolverVista($user, $request->input('vista'), $section->id, $subjectId);

        return redirect()->route('conducta.index', [
            'vista' => $vista,
            'section_id' => $section->id,
            'subject_id' => $subjectId,
            'contexto' => $request->boolean('contexto') ? 1 : null,
        ])->with('mensaje', 'Reporte de conducta registrado.');
    }

    public function show(Request $request, ConductaReporte $conducta): View
    {
        abort_unless($this->scope->puedeVer($request->user(), $conducta), 403, 'No tiene acceso a este reporte.');

        $conducta->load(['student', 'section.grade.level', 'section', 'subject', 'teacher', 'registradoPor']);

        return view('conducta.show', ['reporte' => $conducta]);
    }

    /** Historial de conducta de un estudiante (vista tipo ficha). */
    public function estudiante(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->scope->tieneAcceso($user), 403);

        $vista = $this->scope->vistaParaEstudiante($user);
        $estudiantes = $this->scope->estudiantesVisibles($user, $vista);

        $studentId = $request->query('student_id')
            ?: ($estudiantes->count() === 1 ? $estudiantes->first()->id : null);
        $student = $studentId ? $estudiantes->firstWhere('id', (int) $studentId) : null;

        abort_if($request->query('student_id') && ! $student, 403, 'No tiene acceso a ese estudiante.');

        $reportes = collect();
        if ($student) {
            $reportes = $this->scope->query($user, $vista, ['student_id' => $student->id])->get();
        }

        return view('conducta.estudiante', [
            'estudiantes' => $estudiantes,
            'student' => $student,
            'reportes' => $reportes,
            'resumen' => $this->resumenDeColeccion($reportes),
            'filtros' => $request->only(['student_id']),
        ]);
    }

    /** Exporta el listado filtrado o el historial de un estudiante. */
    public function export(Request $request)
    {
        $user = $request->user();
        abort_unless($this->scope->tieneAcceso($user), 403);

        $formato = $request->query('format') === 'excel' ? 'excel' : 'pdf';
        $studentId = $request->filled('student_id') ? (int) $request->query('student_id') : null;

        if ($studentId) {
            $vista = $this->scope->vistaParaEstudiante($user);
            $student = $this->scope->estudiantesVisibles($user, $vista)->firstWhere('id', $studentId);
            abort_unless($student, 403, 'No tiene acceso a ese estudiante.');

            $filas = $this->scope->query($user, $vista, ['student_id' => $student->id])->get();
            $titulo = 'Reporte de Conducta - '.$student->apellidos.' '.$student->nombres;
            $subtitulo = 'Curso: '.($student->activeSection()?->codigoCorto() ?? '—');
            $nombre = 'conducta-'.$student->codigo;
        } else {
            $sectionId = $request->filled('section_id') ? (int) $request->query('section_id') : null;
            $subjectId = $request->filled('subject_id') ? (int) $request->query('subject_id') : null;
            $vista = $this->scope->resolverVista($user, $request->query('vista'), $sectionId, $subjectId);
            $cursos = $this->scope->cursosVisibles($user, $vista);

            if ($sectionId && ! $cursos->pluck('id')->contains($sectionId)) {
                abort(403, 'No tiene acceso a ese curso.');
            }

            $filtros = [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
                'student_id' => null,
                'desde' => $request->query('desde'),
                'hasta' => $request->query('hasta'),
            ];

            $filas = $this->scope->query($user, $vista, $filtros)->get();
            $section = $sectionId ? $cursos->firstWhere('id', $sectionId) : null;
            $titulo = 'Reporte de Conducta - '.($section?->codigoCorto() ?? 'Todos los cursos');
            $subtitulo = $this->descripcionVista($vista, $user);
            $nombre = 'conducta-'.($section ? 'curso-'.$section->id : 'general');
        }

        return $formato === 'excel'
            ? $this->exportExcel($titulo, $subtitulo, $nombre, $filas)
            : $this->exportPdf($titulo, $subtitulo, $nombre, $filas);
    }

    // ------------------------------------------------------------- internos

    private function tabs($user): array
    {
        $labels = [
            'mios' => 'Mis reportes',
            'paralelo' => 'Mi paralelo',
            'general' => 'Conducta general',
        ];

        $tabs = [];
        foreach ($this->scope->vistasPermitidas($user) as $vista) {
            if (isset($labels[$vista])) {
                $tabs[$vista] = $labels[$vista];
            }
        }

        return $tabs;
    }

    private function descripcionVista(string $vista, $user): string
    {
        return match ($vista) {
            'mios' => 'Reportes que usted registró en sus cursos.',
            'paralelo' => 'Todos los reportes del paralelo que tiene a cargo, de todas las materias.',
            'general' => $this->scope->esDirector($user) && $this->scope->nivelesDirector($user)->isNotEmpty()
                ? 'Reportes de conducta del nivel a su cargo: '.$user->nivelesACargo()->pluck('nombre')->implode(', ').'.'
                : 'Todos los reportes de conducta del colegio.',
            default => 'Historial de conducta.',
        };
    }

    private function resumen(Builder $q): array
    {
        return [
            'total' => (clone $q)->count(),
            'estudiantes' => (clone $q)->distinct()->count('student_id'),
            'ultima' => (clone $q)->max('fecha'),
        ];
    }

    private function resumenDeColeccion($reportes): array
    {
        return [
            'total' => $reportes->count(),
            'estudiantes' => $reportes->pluck('student_id')->unique()->count(),
            'ultima' => $reportes->max('fecha'),
        ];
    }

    private function bloqueActual()
    {
        $teacher = auth()->user()->teacher
            ?? Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return null;
        }

        $ahora = Carbon::now('America/La_Paz');
        $dias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO'];
        $dia = $dias[$ahora->dayOfWeekIso] ?? null;
        if (! $dia) {
            return null;
        }

        $hora = $ahora->format('H:i:s');

        return Schedule::where('teacher_id', $teacher->id)
            ->where('dia', $dia)
            ->where('estado', 'ACTIVO')
            ->whereNotNull('section_id')
            ->orderBy('hora_inicio')
            ->get()
            ->first(fn ($s) => $s->hora_inicio && $s->hora_fin
                && $s->hora_inicio->format('H:i:s') <= $hora
                && $hora <= $s->hora_fin->format('H:i:s'));
    }

    private function exportPdf(string $titulo, string $subtitulo, string $nombre, $filas)
    {
        $pdf = Pdf::loadView('conducta.pdf', compact('titulo', 'subtitulo', 'filas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($nombre.'.pdf');
    }

    private function exportExcel(string $titulo, string $subtitulo, string $nombre, $filas): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Conducta');
        $sheet->setCellValue('A1', $titulo);
        $sheet->setCellValue('A2', $subtitulo);

        $cabecera = ['N.º', 'Fecha', 'Hora', 'Estudiante', 'Curso', 'Materia', 'Descripción', 'Reportado por'];
        $filaCabecera = 4;
        foreach ($cabecera as $i => $texto) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$filaCabecera, $texto);
        }

        $fila = $filaCabecera + 1;
        foreach ($filas as $i => $r) {
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $i + 1);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->fecha->format('d/m/Y'));
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->hora ? substr((string) $r->hora, 0, 5) : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->student->apellidos.' '.$r->student->nombres);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->section->codigo ?? $r->section->nombre ?? '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $r->subject->nombre ?? 'General');
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
}
