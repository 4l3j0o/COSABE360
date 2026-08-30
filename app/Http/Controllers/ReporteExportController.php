<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\GradeScore;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\StudentDocument;
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

    private function pdfResponse(string $titulo, string $bodyHtml, string $filename): \Illuminate\Http\Response
    {
        $html = '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;font-size:11px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #aaa;padding:6px;text-align:left;}th{background:#1e3a8a;color:#fff;}</style></head><body>' . $this->headerHtml($titulo) . $bodyHtml . '</body></html>';
        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
        ]);
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
        $format = $request->query('format', 'pdf');
        $query = GradeScore::with(['assessment.subject', 'assessment.academicYear', 'student'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('academic_year_id', $v)))
            ->when($request->subject_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('subject_id', $v)))
            ->orderByDesc('id')->limit(500)->get();

        if ($format === 'excel') {
            $headers = ['Estudiante', 'Código', 'Evaluación', 'Materia', 'Nota', 'Gestión'];
            $rows = $query->map(fn ($s) => [
                $s->student ? $s->student->nombres . ' ' . $s->student->apellidos : 'N/A',
                $s->student?->codigo ?? '',
                $s->assessment?->nombre ?? '',
                $s->assessment?->subject?->nombre ?? '',
                $s->nota,
                $s->assessment?->academicYear?->nombre ?? '',
            ]);
            return $this->excelResponse('reporte_calificaciones_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($s) => '<tr><td>' . e($s->student?->nombres . ' ' . $s->student?->apellidos) . '</td><td>' . e($s->student?->codigo) . '</td><td>' . e($s->assessment?->nombre) . '</td><td>' . e($s->assessment?->subject?->nombre) . '</td><td>' . e($s->nota) . '</td><td>' . e($s->assessment?->academicYear?->nombre) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Evaluación</th><th>Materia</th><th>Nota</th><th>Gestión</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfResponse('Reporte Calificaciones por Curso/Materia', $body, 'reporte_calificaciones_' . date('Ymd_His'));
    }

    public function academicoPromedios(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $query = GradeScore::with(['assessment.subject', 'student'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('academic_year_id', $v)))
            ->get();

        $promedios = $query->groupBy('student_id')->map(function ($scores) {
            $s = $scores->first()->student;
            $avg = round($scores->avg('nota'), 2);
            $porMateria = $scores->groupBy(fn ($x) => $x->assessment?->subject?->nombre ?? 'Sin materia')->map(fn ($g) => round($g->avg('nota'), 2));
            return ['estudiante' => $s ? $s->nombres . ' ' . $s->apellidos : 'N/A', 'codigo' => $s?->codigo ?? '', 'promedio' => $avg, 'por_materia' => $porMateria->implode(', ')];
        })->values();

        if ($format === 'excel') {
            $headers = ['Estudiante', 'Código', 'Promedio General', 'Por Materia'];
            $rows = $promedios->map(fn ($p) => [$p['estudiante'], $p['codigo'], $p['promedio'], $p['por_materia']]);
            return $this->excelResponse('reporte_promedios_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $promedios->map(fn ($p) => '<tr><td>' . e($p['estudiante']) . '</td><td>' . e($p['codigo']) . '</td><td>' . e($p['promedio']) . '</td><td>' . e($p['por_materia']) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Promedio</th><th>Por Materia</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfResponse('Reporte Promedios por Estudiante', $body, 'reporte_promedios_' . date('Ymd_His'));
    }

    public function administrativoInscripciones(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $query = Enrollment::with(['student', 'academicYear'])->when($request->academic_year_id, fn ($q, $v) => $q->where('academic_year_id', $v))->limit(500)->get();
        if ($format === 'excel') {
            $headers = ['Estudiante', 'Gestión', 'Fecha', 'Estado'];
            $rows = $query->map(fn ($e) => [$e->student ? $e->student->nombres . ' ' . $e->student->apellidos : 'N/A', $e->academicYear?->nombre ?? '', $e->fecha ?? '', $e->estado]);
            return $this->excelResponse('reporte_inscripciones_' . date('Ymd_His'), $headers, $rows);
        }
        $rowsHtml = $query->map(fn ($e) => '<tr><td>' . e($e->student?->nombres . ' ' . $e->student?->apellidos) . '</td><td>' . e($e->academicYear?->nombre) . '</td><td>' . e($e->fecha) . '</td><td>' . e($e->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Gestión</th><th>Fecha</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfResponse('Reporte Inscripciones', $body, 'reporte_inscripciones_' . date('Ymd_His'));
    }

    public function administrativoPagos(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $query = MonthlyPayment::with(['registration.enrollment.student'])->when($request->estado, fn ($q, $v) => $q->where('estado', $v))->limit(500)->get();
        if ($format === 'excel') {
            $headers = ['Estudiante', 'Mes/Año', 'Monto Esperado', 'Monto Pagado', 'Estado'];
            $rows = $query->map(fn ($p) => [$p->registration?->enrollment?->student ? $p->registration->enrollment->student->nombres . ' ' . $p->registration->enrollment->student->apellidos : 'N/A', $p->mes . '/' . $p->anio, $p->monto_esperado, $p->monto_pagado, $p->estado]);
            return $this->excelResponse('reporte_pagos_' . date('Ymd_His'), $headers, $rows);
        }
        $rowsHtml = $query->map(fn ($p) => '<tr><td>' . e($p->registration?->enrollment?->student?->nombres . ' ' . $p->registration?->enrollment?->student?->apellidos) . '</td><td>' . e($p->mes . '/' . $p->anio) . '</td><td>' . e($p->monto_esperado) . '</td><td>' . e($p->monto_pagado) . '</td><td>' . e($p->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Mes/Año</th><th>Monto Esperado</th><th>Monto Pagado</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfResponse('Reporte Pagos y Deudas', $body, 'reporte_pagos_' . date('Ymd_His'));
    }

    public function administrativoDocumentacion(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $query = StudentDocument::with(['student'])->when($request->estado, fn ($q, $v) => $q->where('estado', $v))->limit(500)->get();
        if ($format === 'excel') {
            $headers = ['Estudiante', 'Tipo Documento', 'Estado', 'Observaciones'];
            $rows = $query->map(fn ($d) => [$d->student ? $d->student->nombres . ' ' . $d->student->apellidos : 'N/A', $d->tipo_documento, $d->estado, $d->observaciones ?? '']);
            return $this->excelResponse('reporte_documentacion_' . date('Ymd_His'), $headers, $rows);
        }
        $rowsHtml = $query->map(fn ($d) => '<tr><td>' . e($d->student?->nombres . ' ' . $d->student?->apellidos) . '</td><td>' . e($d->tipo_documento) . '</td><td>' . e($d->estado) . '</td><td>' . e($d->observaciones) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Tipo</th><th>Estado</th><th>Observaciones</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfResponse('Reporte Documentación Pendiente', $body, 'reporte_documentacion_' . date('Ymd_His'));
    }
}
