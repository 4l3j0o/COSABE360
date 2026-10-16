<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SectionSubject;
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
 * HU-57: el reporte exige elegir curso/materia primero (sin autoselección);
 * se retira el filtro de período de asistencia (siempre trimestre);
 * exports PDF/Excel con estructura y membrete institucional.
 */
class EvaluationReportController extends Controller
{
    private const ESTADOS_ASISTENCIA = ['PRESENTE', 'AUSENTE', 'LICENCIA', 'RETRASO', 'RETIRO_JUSTIFICADO'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);

        // HU-81: acepta paralelo+materia por separado (además del combinado).
        $ssid = $this->resolverSectionSubjectId($request, $sectionSubjects);
        if ($ssid) {
            $request->merge(['section_subject_id' => $ssid]);
        }

        $resultado = $this->build($request, $user, $sectionSubjects);

        return view('evaluation-reports.index', [
            'sectionSubjects' => $sectionSubjects,
            'cursos' => $this->cursosDe($sectionSubjects),
            'filtros' => $request->only(['section_subject_id', 'section_id', 'subject_id', 'trimestre']),
            'resultado' => $resultado,
        ]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $sectionSubjects = $this->sectionSubjectsPermitidos($user);

        // HU-81: acepta paralelo+materia por separado (además del combinado).
        $ssid = $this->resolverSectionSubjectId($request, $sectionSubjects);
        if ($ssid) {
            $request->merge(['section_subject_id' => $ssid]);
        }

        if (! $request->query('section_subject_id')) {
            return back()->with('error', 'Primero selecciona el curso y la materia para exportar el reporte.');
        }

        $resultado = $this->build($request, $user, $sectionSubjects);

        $formato = $request->query('format', 'pdf');

        if ($formato === 'excel') {
            return $this->exportExcel($resultado);
        }

        return $this->exportPdf($resultado);
    }

    private function build(Request $request, $user, Collection $sectionSubjects): array
    {
        $sectionSubjectId = $request->query('section_subject_id');
        $trimestre = (int) ($request->query('trimestre') ?: 1);
        $trimestre = in_array($trimestre, [1, 2, 3], true) ? $trimestre : 1;

        // HU-57: sin curso elegido no se genera nada; la vista pide elegir primero.
        if (! $sectionSubjectId) {
            return [
                'filas' => collect(),
                'curso' => null,
                'trimestre' => $trimestre,
                'resumen' => [],
                'top' => collect(),
                'requiere_curso' => true,
            ];
        }

        $cs = SectionSubject::with(['subject', 'section.grade', 'section', 'section.gestion', 'teacher'])
            ->find($sectionSubjectId);

        if ($cs && ! $sectionSubjects->contains('id', $cs->id)) {
            abort(403, 'No tiene acceso a ese curso.');
        }

        if (! $cs) {
            return ['filas' => collect(), 'curso' => null, 'trimestre' => $trimestre, 'resumen' => [], 'top' => collect(), 'requiere_curso' => true];
        }

        $plan = EvaluationPlan::where('section_subject_id', $cs->id)
            ->where('trimestre', $trimestre)
            ->with('criteria.activities')
            ->first();

        $students = $cs->section->students()
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

        // Métricas de asistencia del trimestre por estudiante (HU-57: sin filtro de período)
        $asistencia = $this->asistencia($cs, $students, $trimestre);
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
     * HU-81: resuelve el section_subject desde el combinado o desde
     * paralelo+materia por separado. Con una sola asignación, directo.
     */
    private function resolverSectionSubjectId(Request $request, Collection $sectionSubjects): ?int
    {
        if ($request->query('section_subject_id')) {
            return (int) $request->query('section_subject_id');
        }

        if ($sectionSubjects->count() === 1) {
            return (int) $sectionSubjects->first()->id;
        }

        $sec = (int) $request->query('section_id');
        $mat = (int) $request->query('subject_id');
        if ($sec && $mat) {
            $cs = $sectionSubjects->first(fn ($c) => (int) $c->section_id === $sec && (int) $c->subject_id === $mat);

            return $cs ? (int) $cs->id : null;
        }

        return null;
    }

    /**
     * HU-81: paralelos únicos de las asignaciones (para el filtro en cascada).
     */
    private function cursosDe(Collection $sectionSubjects): Collection
    {
        return $sectionSubjects->map(fn ($cs) => $cs->section)->unique('id')->values();
    }

    /**
     * Métricas de asistencia del trimestre por estudiante.
     */
    private function asistencia(SectionSubject $cs, Collection $students, int $trimestre): array
    {
        $desde = match ($trimestre) {
            2 => now()->startOfYear()->addMonths(3),
            3 => now()->startOfYear()->addMonths(6),
            default => now()->startOfYear(),
        };
        $hasta = now();

        $attendance = Attendance::whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where('section_id', $cs->section_id)
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

    private function sectionSubjectsPermitidos($user): Collection
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return SectionSubject::with(['subject', 'section.grade', 'section', 'section.gestion'])
                ->orderBy('section_id')->orderBy('subject_id')->get();
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            return $user->teacher->sectionSubjects()
                ->with(['subject', 'section.grade', 'section', 'section.gestion'])
                ->orderBy('section_id')->orderBy('subject_id')->get();
        }

        return collect();
    }

    private function exportPdf(array $resultado): StreamedResponse
    {
        $logoPath = public_path('images/logo-colegio-thumb.png');
        $logo = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('evaluation-reports.pdf', compact('resultado', 'logo'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $this->exportFileName($resultado, 'pdf'),
            ['Content-Type' => 'application/pdf']
        );
    }

    private function exportExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $curso = $resultado['curso'];
        $nombreCurso = $curso?->section?->codigoCorto() ?? '—';
        $nombreMateria = $curso?->subject->nombre ?? '—';
        $gestion = $curso?->section?->gestion?->nombre ?? (string) now()->year;
        $docente = $curso?->teacher
            ? trim(($curso->teacher->nombres ?? '').' '.($curso->teacher->apellidos ?? ''))
            : '—';

        $ultCol = $this->colLetter(11); // A..K

        // Membrete institucional
        $sheet->setCellValue('C1', 'UNIDAD EDUCATIVA SAN BERNARDO — TARIJA');
        $sheet->mergeCells('C1:'.$ultCol.'1');
        $sheet->setCellValue('C2', 'Reporte de Evaluación — Aprobados, reprobados, mejores calificaciones y asistencia');
        $sheet->mergeCells('C2:'.$ultCol.'2');
        $sheet->setCellValue('C3', "Curso: {$nombreCurso}   ·   Materia: {$nombreMateria}   ·   Trimestre {$resultado['trimestre']}   ·   Gestión {$gestion}");
        $sheet->mergeCells('C3:'.$ultCol.'3');
        $sheet->setCellValue('C4', 'Docente: '.$docente.'   ·   Generado: '.now()->format('d/m/Y H:i'));
        $sheet->mergeCells('C4:'.$ultCol.'4');

        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF003200');
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF003200');
        $sheet->getStyle('C3:C4')->getFont()->setSize(10)->getColor()->setARGB('FF33412F');

        // Resumen
        $r = $resultado['resumen'];
        $sheet->setCellValue('A6', "Aprobados: {$r['aprobados']}   ·   Reprobados: {$r['reprobados']}   ·   Sin nota: {$r['sin_nota']}   ·   Total: {$r['total']}   ·   Mínimo de aprobación: 51");
        $sheet->mergeCells('A6:'.$ultCol.'6');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF6FAF6');

        // Cabecera del detalle
        $cabecera = ['N.º', 'Estudiante', 'SE (10%)', 'SA (45%)', 'HA (40%)', 'AU (5%)', 'Nota trim.', 'Valoración', 'Estado', '% Asist. (trim.)', 'Pres./Falt./Atr.'];
        $filaCabecera = 8;
        foreach ($cabecera as $i => $texto) {
            $sheet->setCellValue($this->colLetter($i + 1).$filaCabecera, $texto);
        }
        $rangoCabecera = 'A'.$filaCabecera.':'.$ultCol.$filaCabecera;
        $sheet->getStyle($rangoCabecera)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rangoCabecera)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF003200');
        $sheet->getStyle($rangoCabecera)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($filaCabecera)->setRowHeight(30);

        // Detalle
        $fila = $filaCabecera + 1;
        foreach ($resultado['filas'] as $i => $f) {
            $detalle = $f->asistencia['detalle'] ?? [];
            $asistCorta = 'P:'.($detalle['PRESENTE'] ?? 0).' A:'.($detalle['AUSENTE'] ?? 0).' R:'.($detalle['RETRASO'] ?? 0);

            $sheet->setCellValue('A'.$fila, $i + 1);
            $sheet->setCellValue('B'.$fila, $f->student->apellidos.' '.$f->student->nombres);
            $sheet->setCellValue('C'.$fila, $f->criterios['SE'] ?? '');
            $sheet->setCellValue('D'.$fila, $f->criterios['SA'] ?? '');
            $sheet->setCellValue('E'.$fila, $f->criterios['HA'] ?? '');
            $sheet->setCellValue('F'.$fila, $f->criterios['AU'] ?? '');
            $sheet->setCellValue('G'.$fila, $f->nota ?? '');
            $sheet->setCellValue('H'.$fila, $f->codigo);
            $sheet->setCellValue('I'.$fila, $f->aprobado === null ? 'Sin nota' : ($f->aprobado ? 'Aprobado' : 'Reprobado'));
            $sheet->setCellValue('J'.$fila, $f->asistencia['porcentaje'] ?? '');
            $sheet->setCellValue('K'.$fila, $asistCorta);

            if ($f->aprobado === true) {
                $sheet->getStyle('I'.$fila)->getFont()->setBold(true)->getColor()->setARGB('FF14532D');
            } elseif ($f->aprobado === false) {
                $sheet->getStyle('I'.$fila)->getFont()->setBold(true)->getColor()->setARGB('FF7F1D1D');
            }

            // Zebra institucional
            if ($fila % 2 === 0) {
                $sheet->getStyle('A'.$fila.':'.$ultCol.$fila)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F6F2');
            }

            $fila++;
        }

        // Mejores calificaciones
        $fila++;
        $sheet->setCellValue('A'.$fila, 'Mejores calificaciones del trimestre');
        $sheet->mergeCells('A'.$fila.':'.$ultCol.$fila);
        $sheet->getStyle('A'.$fila)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A'.$fila)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF003200');
        $fila++;
        $sheet->setCellValue('A'.$fila, 'Puesto');
        $sheet->setCellValue('B'.$fila, 'Estudiante');
        $sheet->setCellValue('C'.$fila, 'Nota');
        $sheet->setCellValue('D'.$fila, 'Valoración');
        $sheet->getStyle('A'.$fila.':D'.$fila)->getFont()->setBold(true);
        $fila++;
        foreach ($resultado['top'] as $i => $f) {
            $sheet->setCellValue('A'.$fila, '#'.($i + 1));
            $sheet->setCellValue('B'.$fila, $f->student->apellidos.' '.$f->student->nombres);
            $sheet->setCellValue('C'.$fila, $f->nota);
            $sheet->setCellValue('D'.$fila, $f->codigo);
            $fila++;
        }

        // Bordes + anchos + congelar
        $sheet->getStyle('A'.$filaCabecera.':'.$ultCol.($fila - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFD1DFD1');
        foreach (range(1, count($cabecera)) as $c) {
            $sheet->getColumnDimension($this->colLetter($c))->setAutoSize(true);
        }
        $sheet->freezePane('A'.($filaCabecera + 1));
        $sheet->getSheetView()->setZoomScale(90);

        // Logo institucional centrado y agrandado (HU-57)
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(34);
        $this->insertarLogoCentrado($sheet, 'A', 'B', 84, 2);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $contenido = (string) ob_get_clean();

        return response()->streamDownload(
            fn () => print ($contenido),
            $this->exportFileName($resultado, 'xlsx'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * Nombre de archivo del export con curso y trimestre.
     */
    private function exportFileName(array $resultado, string $extension): string
    {
        $base = 'reporte-evaluacion';
        if ($resultado['curso']) {
            $codigo = $resultado['curso']->section->codigo ?? $resultado['curso']->section->nombre ?? '';
            $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $codigo), '-'));
            if ($slug !== '') {
                $base .= '_'.$slug;
            }
            $base .= '_T'.$resultado['trimestre'];
        }

        return $base.'_'.now()->format('Ymd').'.'.$extension;
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
