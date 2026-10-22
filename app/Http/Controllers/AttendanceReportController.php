<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    /** Estados válidos de asistencia (HU-71). */
    private const ESTADOS = ['PRESENTE', 'AUSENTE', 'LICENCIA', 'RETRASO', 'RETIRO_JUSTIFICADO'];

    /**
     * HU-44: reporte de asistencia por día, semana, mes o trimestre.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cursos = $this->cursosPermitidos($user);

        // Con un solo curso permitido se preselecciona directo
        if (! $request->query('section_id') && $cursos->count() === 1) {
            $request->merge(['section_id' => $cursos->first()->id]);
        }

        $filtros = $request->only(['rango', 'fecha', 'section_id', 'subject_id']);

        $resultado = $this->build($request, $user, $cursos);

        return view('attendance-reports.index', [
            'cursos' => $cursos,
            'materias' => $resultado['materias'],
            'filtros' => $filtros,
            'resultado' => $resultado,
        ]);
    }

    /**
     * HU-44: exportación PDF/Excel del reporte de asistencia.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $cursos = $this->cursosPermitidos($user);

        if ($this->requiereCurso($user) && ! $request->query('section_id')) {
            return back()->with('error', 'Selecciona un curso para generar el reporte.');
        }

        // Con un solo curso permitido se preselecciona directo
        if (! $request->query('section_id') && $cursos->count() === 1) {
            $request->merge(['section_id' => $cursos->first()->id]);
        }

        $resultado = $this->build($request, $user, $cursos);

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportExcel($resultado);
        }

        return $this->exportPdf($resultado);
    }

    private function build(Request $request, $user, $cursos)
    {
        $fecha = $request->query('fecha') ? \Carbon\Carbon::parse($request->query('fecha')) : now();
        $rango = $request->query('rango', 'mes');
        $sectionId = $request->query('section_id');
        $subjectId = $request->query('subject_id');

        [$desde, $hasta] = $this->rangoFechas($rango, $fecha);

        $section = $sectionId ? Section::with(['grade', 'gestion'])->find($sectionId) : null;

        if ($section && ! $this->permiteCurso($user, $section->id, $cursos)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        // Materias del curso elegido (o de todos los permitidos si no hay curso fijo)
        $materias = SectionSubject::with('subject')
            ->when($sectionId,
                fn ($q) => $q->where('section_id', $sectionId),
                fn ($q) => $q->whereIn('section_id', $cursos->pluck('id')))
            ->get()
            ->unique('subject_id')
            ->values();

        // Docente y tutor trabajan siempre sobre un curso concreto
        if ($this->requiereCurso($user) && ! $sectionId) {
            return [
                'fecha' => $fecha->format('d/m/Y'),
                'rango' => $rango,
                'desde' => $desde->format('d/m/Y'),
                'hasta' => $hasta->format('d/m/Y'),
                'curso' => null,
                'materia' => null,
                'diasClase' => collect(),
                'filas' => collect(),
                'estados' => self::ESTADOS,
                'materias' => $materias,
                'requiere_curso' => true,
            ];
        }

        // Estudiantes matriculados (ACTIVA) del curso (o todos si no se elige curso)
        // Registration -> enrollment -> estudiante (no existe relación directa `student`)
        $studentsQuery = Registration::where('estado', 'ACTIVA')
            ->with('enrollment.estudiante')
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->whereHas('enrollment.estudiante', fn ($q) => $q->where('estado', 'ACTIVO'));

        $students = $studentsQuery->get()->map(fn ($r) => $r->enrollment?->estudiante)->filter()->unique('id')->values();

        // El estudiante solo ve su propia fila
        if ($user->hasRole('Estudiante') && $user->student) {
            $students = $students->where('id', $user->student->id)->values();
        }

        // Asistencias dentro del rango, opcional por materia.
        // HU-82: solo filas por materia (las "generales" ya no existen ni se cuentan).
        $attendanceQuery = Attendance::whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->whereNotNull('subject_id')
            ->whereIn('estado', self::ESTADOS)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId));

        $attendanceByStudent = $attendanceQuery->get()->groupBy('student_id');

        // Días de clase del curso (por horario) dentro del rango, para el total esperado
        $diasClase = Schedule::where('estado', 'ACTIVO')
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->pluck('dia')
            ->map(fn ($d) => strtolower($d))
            ->unique()
            ->values();

        $filas = $students->map(function ($student) use ($attendanceByStudent, $desde, $hasta, $diasClase, $sectionId) {
            $registros = $attendanceByStudent->get($student->id, collect());
            $conteo = [];
            foreach (self::ESTADOS as $e) {
                $conteo[$e] = $registros->where('estado', $e)->count();
            }
            $total = array_sum($conteo);
            $asistio = $conteo['PRESENTE'] + $conteo['LICENCIA'] + $conteo['RETRASO'] + $conteo['RETIRO_JUSTIFICADO'];
            $porcentaje = $total > 0 ? round($asistio / $total * 100, 1) : 0;

            return [
                'codigo' => $student->codigo,
                'nombre' => $student->nombres.' '.$student->apellidos,
                'conteo' => $conteo,
                'total' => $total,
                'asistio' => $asistio,
                'porcentaje' => $porcentaje,
            ];
        })->sortByDesc('porcentaje')->values();

        $curso = null;
        if ($section) {
            $curso = $section->codigoCorto();
        } elseif ($sectionId) {
            $curso = 'Curso #'.$sectionId;
        } else {
            $curso = 'Todos los cursos';
        }

        $materia = null;
        if ($subjectId) {
            $materia = Subject::where('id', $subjectId)
                ->whereIn('id', $materias->pluck('subject_id'))
                ->value('nombre') ?? 'Materia #'.$subjectId;
        }

        return [
            'fecha' => $fecha->format('d/m/Y'),
            'rango' => $rango,
            'desde' => $desde->format('d/m/Y'),
            'hasta' => $hasta->format('d/m/Y'),
            'curso' => $curso,
            'materia' => $materia,
            'diasClase' => $diasClase,
            'filas' => $filas,
            'estados' => self::ESTADOS,
            'materias' => $materias,
            'requiere_curso' => false,
        ];
    }

    private function rangoFechas(string $rango, \Carbon\Carbon $fecha): array
    {
        return match ($rango) {
            'dia' => [$fecha->copy()->startOfDay(), $fecha->copy()->endOfDay()],
            'semana' => [$fecha->copy()->startOfWeek(), $fecha->copy()->endOfWeek()],
            'mes' => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()],
            'trimestre' => [$fecha->copy()->subMonths(2)->startOfMonth(), $fecha->copy()->endOfMonth()],
            default => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()],
        };
    }

    private function cursosPermitidos($user)
    {
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
            return Section::with(['grade', 'gestion'])->ordenadoPorNivel()->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->sectionSubjects()
                ->with(['section.grade', 'section', 'section.gestion', 'subject'])
                ->get()
                ->map(fn ($cs) => $cs->section)
                ->unique('id')
                ->pipe(fn ($c) => Section::ordenarPorNivel($c));
        }

        if ($user->hasRole('Estudiante') && $user->student) {
            $curso = $user->student->activeSection();

            return $curso ? collect([$curso]) : collect();
        }

        return collect();
    }

    /**
     * Docente y estudiante siempre reportan sobre un curso concreto (nada de "todos").
     */
    private function requiereCurso($user): bool
    {
        return $user->hasRole('Docente') || $user->hasRole('Estudiante');
    }

    private function permiteCurso($user, int $sectionId, $cursos): bool
    {
        if ($user->hasAnyRole(['Administrador', 'Director'])) {
            return true;
        }

        return $cursos->contains('id', $sectionId);
    }

    private function exportPdf(array $resultado)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance-reports.pdf', compact('resultado'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('reporte-asistencia-'.$resultado['desde'].'.pdf');
    }

    private function exportExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencia');

        $titulo = 'Reporte de Asistencia - '.$resultado['curso'];
        if ($resultado['materia']) {
            $titulo .= ' - '.$resultado['materia'];
        }
        $titulo .= ' ('.$resultado['desde'].' al '.$resultado['hasta'].')';

        $sheet->setCellValue('A1', $titulo);

        $cabecera = ['N.º', 'Código', 'Estudiante', 'Presente', 'Ausente', 'Licencia', 'Retraso', 'Retiro just.', 'Total', 'Asistencia (%)'];
        $filaCabecera = 3;
        foreach ($cabecera as $i => $texto) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$filaCabecera, $texto);
        }

        $fila = $filaCabecera + 1;
        foreach ($resultado['filas'] as $i => $filaDatos) {
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $i + 1);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['codigo']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['nombre']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['conteo']['PRESENTE']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['conteo']['AUSENTE']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['conteo']['RETRASO']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['conteo']['LICENCIA']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['conteo']['RETIRO_JUSTIFICADO']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['total']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++).$fila, $filaDatos['porcentaje']);
            $fila++;
        }

        $ultimaLetra = Coordinate::stringFromColumnIndex(count($cabecera));
        $sheet->getStyle('A'.$filaCabecera.':'.$ultimaLetra.$filaCabecera)->getFont()->setBold(true);
        foreach (range(1, count($cabecera)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        $filename = 'reporte-asistencia-'.str_replace('/', '-', $resultado['desde']).'.xlsx';

        return response()->streamDownload(
            fn () => print (ob_get_clean()),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function colLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }
}