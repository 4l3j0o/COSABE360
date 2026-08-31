<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\GradeScore;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\StudentDocument;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteExportController extends Controller
{
    private function headerHtml(string $titulo): string
    {
        $fecha = now()->format('d/m/Y H:i');
        $logo = asset('images/logo-colegio.png');
        return <<<HTML
<div style="text-align:center;border-bottom:2px solid #1e3a8a;padding:10px;margin-bottom:15px;">
<img src="{$logo}" alt="Logo" style="height:50px;" onerror="this.style.display='none'">
<h2 style="margin:5px 0;color:#1e3a8a;">Unidad Educativa San Bernardo</h2>
<h3 style="margin:0;">{$titulo}</h3>
<p style="font-size:11px;color:#555;">Fecha de generación: {$fecha}</p>
</div>
HTML;
    }

    private function pdfDownload(string $titulo, string $bodyHtml, string $filename): \Illuminate\Http\Response
    {
        $html = '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;font-size:11px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #aaa;padding:6px;text-align:left;}th{background:#1e3a8a;color:#fff;}</style></head><body>' . $this->headerHtml($titulo) . $bodyHtml . '</body></html>';

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    private function excelResponse(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $fecha = now()->format('d/m/Y H:i');
        return response()->streamDownload(function () use ($headers, $rows, $fecha) {
            $out = fopen('php://output', 'w');
            fwrite($out, "Unidad Educativa San Bernardo\n");
            fwrite($out, "Fecha de generación: {$fecha}\n\n");
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    public function academicoCalificaciones(Request $request)
    {
        $query = GradeScore::with(['assessment.subject', 'assessment.academicYear', 'assessment.courseSubject.course', 'student'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('academic_year_id', $v)))
            ->when($request->subject_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('subject_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->whereHas('assessment.courseSubject', fn ($cq) => $cq->where('course_id', $v)))
            ->orderByDesc('id')->limit(500)->get();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Evaluación', 'Materia', 'Curso', 'Nota', 'Gestión'];
            $rows = $query->map(fn ($s) => [
                $s->student ? $s->student->nombres . ' ' . $s->student->apellidos : 'N/A',
                $s->student?->codigo ?? '',
                $s->assessment?->nombre ?? '',
                $s->assessment?->subject?->nombre ?? '',
                $s->assessment?->courseSubject?->course?->nombre ?? '',
                $s->nota,
                $s->assessment?->academicYear?->nombre ?? '',
            ]);
            return $this->excelResponse('reporte_calificaciones_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($s) => '<tr><td>' . e($s->student?->nombres . ' ' . $s->student?->apellidos) . '</td><td>' . e($s->student?->codigo) . '</td><td>' . e($s->assessment?->nombre) . '</td><td>' . e($s->assessment?->subject?->nombre) . '</td><td>' . e($s->assessment?->courseSubject?->course?->nombre) . '</td><td>' . e($s->nota) . '</td><td>' . e($s->assessment?->academicYear?->nombre) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Evaluación</th><th>Materia</th><th>Curso</th><th>Nota</th><th>Gestión</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Calificaciones por Curso/Materia', $body, 'reporte_calificaciones_' . date('Ymd_His'));
    }

    public function academicoPromedios(Request $request)
    {
        $query = GradeScore::with(['assessment.subject', 'student'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('academic_year_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->whereHas('assessment.courseSubject', fn ($cq) => $cq->where('course_id', $v)))
            ->get();

        $promedios = $query->groupBy('student_id')->map(function ($scores) {
            $s = $scores->first()->student;
            $porMateria = $scores->groupBy(fn ($x) => $x->assessment?->subject?->nombre ?? 'Sin materia')->map(function ($g) {
                $sumPesos = $g->sum('assessment.peso');
                $prom = $sumPesos > 0 ? round($g->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2) : round($g->avg('nota'), 2);
                return ['materia' => $g->first()->assessment?->subject?->nombre ?? 'Sin materia', 'promedio' => $prom];
            });
            $promGeneral = round($porMateria->avg('promedio'), 2);
            return ['estudiante' => $s ? $s->nombres . ' ' . $s->apellidos : 'N/A', 'codigo' => $s?->codigo ?? '', 'promedio' => $promGeneral, 'por_materia' => $porMateria->values()];
        })->values();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Promedio General'];
            $rows = $promedios->map(fn ($p) => [$p['estudiante'], $p['codigo'], $p['promedio']]);
            return $this->excelResponse('reporte_promedios_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $promedios->map(function ($p) {
            $materias = $p['por_materia']->map(fn ($m) => e($m['materia']) . ': ' . $m['promedio'])->implode(', ');
            return '<tr><td>' . e($p['estudiante']) . '</td><td>' . e($p['codigo']) . '</td><td>' . e($p['promedio']) . '</td><td>' . $materias . '</td></tr>';
        })->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Promedio</th><th>Por Materia</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Promedios por Estudiante', $body, 'reporte_promedios_' . date('Ymd_His'));
    }

    public function administrativoInscripciones(Request $request)
    {
        $query = Enrollment::with(['estudiante', 'academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->limit(500)->get();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Gestión', 'Fecha', 'Estado'];
            $rows = $query->map(fn ($e) => [
                $e->estudiante ? $e->estudiante->nombres . ' ' . $e->estudiante->apellidos : 'N/A',
                $e->estudiante?->codigo ?? '',
                $e->academicYear?->nombre ?? '',
                $e->fecha ?? '',
                $e->estado,
            ]);
            return $this->excelResponse('reporte_inscripciones_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($e) => '<tr><td>' . e($e->estudiante?->nombres . ' ' . $e->estudiante?->apellidos) . '</td><td>' . e($e->estudiante?->codigo) . '</td><td>' . e($e->academicYear?->nombre) . '</td><td>' . e($e->fecha) . '</td><td>' . e($e->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Gestión</th><th>Fecha</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Inscripciones', $body, 'reporte_inscripciones_' . date('Ymd_His'));
    }

    public function administrativoMatriculas(Request $request)
    {
        $query = Registration::with(['enrollment.estudiante', 'course.grade', 'course.section', 'enrollment.academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->where('course_id', $v))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('id')->limit(500)->get();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Curso', 'Gestión', 'Estado'];
            $rows = $query->map(fn ($m) => [
                $m->enrollment?->estudiante ? $m->enrollment->estudiante->nombres . ' ' . $m->enrollment->estudiante->apellidos : 'N/A',
                $m->enrollment?->estudiante?->codigo ?? '',
                ($m->course?->grade?->nombre ?? '') . ' ' . ($m->course?->section?->nombre ?? '') . ' - ' . ($m->course?->nombre ?? ''),
                $m->enrollment?->academicYear?->nombre ?? '',
                $m->estado,
            ]);
            return $this->excelResponse('reporte_matriculas_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($m) => '<tr><td>' . e($m->enrollment?->estudiante?->nombres . ' ' . $m->enrollment?->estudiante?->apellidos) . '</td><td>' . e($m->enrollment?->estudiante?->codigo) . '</td><td>' . e(($m->course?->grade?->nombre ?? '') . ' ' . ($m->course?->section?->nombre ?? '') . ' - ' . ($m->course?->nombre ?? '')) . '</td><td>' . e($m->enrollment?->academicYear?->nombre) . '</td><td>' . e($m->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Curso</th><th>Gestión</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Matrículas', $body, 'reporte_matriculas_' . date('Ymd_His'));
    }

    public function administrativoPagos(Request $request)
    {
        $query = MonthlyPayment::with(['registration.enrollment.estudiante', 'registration.course'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('registration.enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->whereHas('registration', fn ($rq) => $rq->where('course_id', $v)))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->limit(500)->get();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Mes/Año', 'Monto Esperado', 'Monto Pagado', 'Estado'];
            $rows = $query->map(fn ($p) => [
                $p->registration?->enrollment?->estudiante ? $p->registration->enrollment->estudiante->nombres . ' ' . $p->registration->enrollment->estudiante->apellidos : 'N/A',
                $p->mes . '/' . $p->anio,
                $p->monto_esperado,
                $p->monto_pagado,
                $p->estado,
            ]);
            return $this->excelResponse('reporte_pagos_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($p) => '<tr><td>' . e($p->registration?->enrollment?->estudiante?->nombres . ' ' . $p->registration?->enrollment?->estudiante?->apellidos) . '</td><td>' . e($p->mes . '/' . $p->anio) . '</td><td>' . e($p->monto_esperado) . '</td><td>' . e($p->monto_pagado) . '</td><td>' . e($p->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Mes/Año</th><th>Monto Esperado</th><th>Monto Pagado</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Pagos y Deudas', $body, 'reporte_pagos_' . date('Ymd_His'));
    }

    public function administrativoDocumentacion(Request $request)
    {
        $query = StudentDocument::with(['student'])
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->when($request->tipo_documento, fn ($q, $v) => $q->where('tipo_documento', $v))
            ->limit(500)->get();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Tipo Documento', 'Estado', 'Observaciones'];
            $rows = $query->map(fn ($d) => [
                $d->student ? $d->student->nombres . ' ' . $d->student->apellidos : 'N/A',
                $d->student?->codigo ?? '',
                $d->tipo_documento,
                $d->estado,
                $d->observaciones ?? '',
            ]);
            return $this->excelResponse('reporte_documentacion_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($d) => '<tr><td>' . e($d->student?->nombres . ' ' . $d->student?->apellidos) . '</td><td>' . e($d->student?->codigo) . '</td><td>' . e($d->tipo_documento) . '</td><td>' . e($d->estado) . '</td><td>' . e($d->observaciones) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Tipo</th><th>Estado</th><th>Observaciones</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Documentación Pendiente', $body, 'reporte_documentacion_' . date('Ymd_His'));
    }
}
