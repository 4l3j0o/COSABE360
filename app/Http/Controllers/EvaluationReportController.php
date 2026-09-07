<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use App\Models\Student;
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
 * HU-49: reportes del docente (aprobados/reprobados, asistencia, mejores calificaciones/promedios)
 * por curso, materia y trimestre, con exportación PDF/Excel.
 */
class EvaluationReportController extends Controller
{
    private const ESTADOS_ASISTENCIA = ['PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $courseSubjects = $this->courseSubjectsPermitidos($user);
        $resultado = $this->build($request, $user, $courseSubjects);

        return view('evaluation-reports.index', [
            'courseSubjects' => $courseSubjects,
            'filtros' => $request->only(['course_subject_id', 'trimestre', 'rango_asistencia']),
            'resultado' => $resultado,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $courseSubjects = $this->courseSubjectsPermitidos($user);
        $resultado = $this->build($request, $user, $courseSubjects);

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportExcel($resultado);
        }

        return $this->exportPdf($resultado);
    }

    private function build(Request $request, $user, Collection $courseSubjects): array
    {
        $courseSubjectId = $request->query('course_subject_id');
        $trimestre = (int) ($request->query('trimestre') ?: 1);

        $cs = $courseSubjectId
            ? CourseSubject::with(['subject', 'course.grade', 'course.section', 'course.gestion', 'teacher'])->find($courseSubjectId)
            : $courseSubjects->first();

        if ($cs && ! $courseSubjects->contains('id', $cs->id)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        if (! $cs) {
            return ['filas' => collect(), 'curso' => null, 'trimestre' => $trimestre, 'resumen' => [], 'top' => []];
        }

        $plan = EvaluationPlan::where('course_subject_id', $cs->id)
            ->where('trimestre', $trimestre)
            ->with('criteria.activities')
            ->first();

        $students = $cs->course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->get();

        $service = app(EvaluationService::class);

        // Filas: nota de trimestre + valoración + estado
        $filas = $students->map(function (Student $student) use ($service, $plan) {
            $notaTrim = $plan ? $service->notaTrimestre($plan, $student) : null;
            $valoracion = $notaTrim !== null ? EvaluationService::valoracion($notaTrim) : null;

            return (object) [
                'student' => $student,
                'nota' => $notaTrim,
                'codigo' => $valoracion['codigo'] ?? '—',
                'nombre_val' => $valoracion['nombre'] ?? '—',
                'aprobado' => $notaTrim !== null ? EvaluationService::aprobado($notaTrim) : null,
                'criterios' => $plan ? $this->notasCriterio($plan, $student, $service) : [],
            ];
        });

        // Métricas de asistencia por estudiante (período elegido)
        $asistencia = $this->asistencia($cs, $students, $request->query('rango_asistencia', 'trimestre'), $trimestre);
        foreach ($filas as $fila) {
            $fila->asistencia = $asistencia[$fila->student->id] ?? ['total' => 0, 'porcentaje' => null, 'detalle' => []];
        }

        // Resumen aprobados/reprobados
        $aprobados = $filas->filter(fn ($f) => $f->aprobado === true)->count();
        $reprobados = $filas->filter(fn ($f) => $f->aprobado === false)->count();
        $sinNota = $filas->filter(fn ($f) => $f->aprobado === null)->count();

        // Mejores calificaciones / promedios (top 5)
        $top = $filas->filter(fn ($f) => $f->nota !== null)
            ->sortByDesc('nota')
            ->take(5)
            ->values();

        return [
            'filas' => $filas,
            'curso' => $cs,
            'trimestre' => $trimestre,
            'resumen' => ['aprobados' => $aprobados, 'reprobados' => $reprobados, 'sin_nota' => $sinNota, 'total' => $filas->count()],
            'top' => $top,
        ];
    }

    private function notasCriterio(EvaluationPlan $plan, Student $student, EvaluationService $service): array
    {
        $notas = [];
        foreach ($plan->criteria as $criterion) {
            $notas[$criterion->codigo] = $service->notaCriterio($criterion, $student);
        }

        return $notas;
    }

    /**
     * Métricas de asistencia por estudiante en un rango/trimestre.
     */
    private function asistencia(CourseSubject $cs, Collection $students, string $rango, int $trimestre): array
    {
        $desde = now()->startOfYear();
        $hasta = now();

        if ($rango === 'trimestre') {
            $desde = match ($trimestre) {
                1 => now()->startOfYear(),
                2 => now()->startOfYear()->addMonths(3),
                3 => now()->startOfYear()->addMonths(6),
                default => now()->startOfYear(),
            };
        } elseif ($rango === 'mes') {
            $desde = now()->startOfMonth();
        } elseif ($rango === 'semana') {
            $desde = now()->startOfWeek();
        }

        $attendance = Attendance::whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where('course_id', $cs->course_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $result = [];
        foreach ($students as $student) {
            $registros = $attendance->get($student->id, collect());
            $detalle = [];
            $total = $registros->count();
            foreach (self::ESTADOS_ASISTENCIA as $estado) {
                $detalle[$estado] = $registros->where('estado', $estado)->count();
            }
            $presente = $registros->where('estado', 'PRESENTE')->count();
            $porcentaje = $total > 0 ? round(($presente / $total) * 100) : null;

            $result[$student->id] = ['total' => $total, 'porcentaje' => $porcentaje, 'detalle' => $detalle];
        }

        return $result;
    }

    private function courseSubjectsPermitidos($user): Collection
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return CourseSubject::with(['subject', 'course.grade', 'course.section', 'course.gestion'])
                ->orderBy('course_id')->orderBy('subject_id')->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->courseSubjects()
                ->with(['subject', 'course.grade', 'course.section', 'course.gestion'])
                ->orderBy('course_id')->orderBy('subject_id')->get();
        }

        return collect();
    }

    private function exportPdf(array $resultado): StreamedResponse
    {
        $pdf = Pdf::loadView('evaluation-reports.pdf', compact('resultado'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'reporte-evaluacion_'.$resultado['trimestre'].'_'.now()->format('Ymd').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function exportExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $curso = $resultado['curso'];
        $sheet->setCellValue('A1', 'Reporte de Evaluación');
        $sheet->setCellValue('A2', ($curso?->course->nombre ?? '').' - '.($curso?->subject->nombre ?? '').' - Trimestre '.$resultado['trimestre']);

        $sheet->setCellValue('A4', 'N.º');
        $sheet->setCellValue('B4', 'Estudiante');
        $cols = ['SE', 'SA', 'HA', 'AU'];
        $colIni = 3;
        foreach ($cols as $i => $c) {
            $sheet->setCellValue($this->colLetter($colIni + $i).'4', $c);
        }
        $sheet->setCellValue($this->colLetter($colIni + 4).'4', 'Nota');
        $sheet->setCellValue($this->colLetter($colIni + 5).'4', 'Valoración');
        $sheet->setCellValue($this->colLetter($colIni + 6).'4', 'Estado');
        $sheet->setCellValue($this->colLetter($colIni + 7).'4', '% Asist');

        $fila = 5;
        foreach ($resultado['filas'] as $i => $f) {
            $sheet->setCellValue('A'.$fila, $i + 1);
            $sheet->setCellValue('B'.$fila, $f->student->apellidos.' '.$f->student->nombres);
            foreach ($cols as $j => $c) {
                $sheet->setCellValue($this->colLetter($colIni + $j).$fila, $f->criterios[$c] ?? '');
            }
            $sheet->setCellValue($this->colLetter($colIni + 4).$fila, $f->nota ?? '');
            $sheet->setCellValue($this->colLetter($colIni + 5).$fila, $f->codigo);
            $sheet->setCellValue($this->colLetter($colIni + 6).$fila, $f->aprobado === null ? '' : ($f->aprobado ? 'Aprobado' : 'Reprobado'));
            $sheet->setCellValue($this->colLetter($colIni + 7).$fila, $f->asistencia['porcentaje'] ?? '');
            $fila++;
        }

        $sheet->getStyle('A4:'.$this->colLetter($colIni + 7).'4')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return response()->streamDownload(
            fn () => print (ob_get_clean()),
            'reporte-evaluacion_'.$resultado['trimestre'].'_'.now()->format('Ymd').'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function colLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }
}
