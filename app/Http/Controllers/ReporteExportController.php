<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\StudentDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteExportController extends Controller
{
    private function headerHtml(string $titulo): string
    {
        $fecha = now()->format('d/m/Y H:i');
        $logoRuta = public_path('images/logo-colegio.png');
        $logoHtml = is_file($logoRuta)
            ? '<td style="width:90px;vertical-align:middle;text-align:left;">'
                . '<img src="' . $logoRuta . '" alt="Logo" style="height:55px;">'
                . '</td>'
            : '';
        return <<<HTML
<table style="width:100%;border-collapse:collapse;border-bottom:2px solid #005100;padding-bottom:10px;margin-bottom:15px;">
<tr>
{$logoHtml}
<td style="vertical-align:middle;text-align:left;">
<h2 style="margin:0;color:#005100;font-size:16px;">Unidad Educativa San Bernardo</h2>
<h3 style="margin:4px 0 0 0;font-size:13px;">{$titulo}</h3>
<p style="margin:4px 0 0 0;font-size:11px;color:#555;">Fecha de generación: {$fecha}</p>
</td>
</tr>
</table>
HTML;
    }

    private function pdfDownload(string $titulo, string $bodyHtml, string $filename): \Illuminate\Http\Response
    {
        $html = '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;font-size:11px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #aaa;padding:6px;text-align:left;}th{background:#005100;color:#fff;}</style></head><body>' . $this->headerHtml($titulo) . $bodyHtml . '</body></html>';

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * HU-58b: Excel real (.xlsx) con columnas y línea visual institucional
     * (nada de CSV con comas). Misma firma que antes para no tocar llamadas.
     */
    private function excelResponse(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $ultCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

        // Membrete
        $sheet->setCellValue('A1', 'UNIDAD EDUCATIVA SAN BERNARDO — TARIJA');
        $sheet->mergeCells('A1:' . $ultCol . '1');
        $sheet->setCellValue('A2', 'Generado: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:' . $ultCol . '2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF003200');
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF33412F');

        // Cabecera
        $filaCabecera = 4;
        foreach ($headers as $i => $texto) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . $filaCabecera, $texto);
        }
        $rangoCabecera = 'A' . $filaCabecera . ':' . $ultCol . $filaCabecera;
        $sheet->getStyle($rangoCabecera)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rangoCabecera)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF003200');
        $sheet->getStyle($rangoCabecera)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Detalle
        $fila = $filaCabecera + 1;
        foreach ($rows as $row) {
            $col = 1;
            foreach ((array) $row as $valor) {
                if ($valor instanceof \DateTimeInterface) {
                    $valor = $valor->format('d/m/Y');
                }
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $fila, $valor ?? '');
            }
            if ($fila % 2 === 0) {
                $sheet->getStyle('A' . $fila . ':' . $ultCol . $fila)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F6F2');
            }
            $fila++;
        }

        $sheet->getStyle('A' . $filaCabecera . ':' . $ultCol . ($fila - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFD1DFD1');
        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
        $sheet->freezePane('A' . ($filaCabecera + 1));

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $contenido = (string) ob_get_clean();

        return response()->streamDownload(
            fn () => print ($contenido),
            $filename . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
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
        $query = Registration::with(['enrollment.estudiante', 'section.grade', 'section', 'enrollment.academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->section_id, fn ($q, $v) => $q->where('section_id', $v))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('id')->limit(500)->get();

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Curso', 'Gestión', 'Estado'];
            $rows = $query->map(fn ($m) => [
                $m->enrollment?->estudiante ? $m->enrollment->estudiante->nombres . ' ' . $m->enrollment->estudiante->apellidos : 'N/A',
                $m->enrollment?->estudiante?->codigo ?? '',
                ($m->section?->codigoCorto() ?? ''),
                $m->enrollment?->academicYear?->nombre ?? '',
                $m->estado,
            ]);
            return $this->excelResponse('reporte_matriculas_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($m) => '<tr><td>' . e($m->enrollment?->estudiante?->nombres . ' ' . $m->enrollment?->estudiante?->apellidos) . '</td><td>' . e($m->enrollment?->estudiante?->codigo) . '</td><td>' . e(($m->section?->codigoCorto() ?? '')) . '</td><td>' . e($m->enrollment?->academicYear?->nombre) . '</td><td>' . e($m->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Código</th><th>Curso</th><th>Gestión</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
        return $this->pdfDownload('Reporte Matrículas', $body, 'reporte_matriculas_' . date('Ymd_His'));
    }

    public function administrativoPagos(Request $request)
    {
        $query = MonthlyPayment::with(['registration.enrollment.estudiante', 'registration.section', 'registration.enrollment.academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('registration.enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->section_id, fn ($q, $v) => $q->whereHas('registration', fn ($rq) => $rq->where('section_id', $v)))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->limit(500)->get();

        $mesNombre = [2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre'];

        if ($request->query('format') === 'excel') {
            $headers = ['Estudiante', 'Código', 'Mensualidad', 'Monto', 'Estado'];
            $rows = $query->map(fn ($p) => [
                $p->registration?->enrollment?->estudiante ? $p->registration->enrollment->estudiante->nombres . ' ' . $p->registration->enrollment->estudiante->apellidos : 'N/A',
                $p->registration?->enrollment?->estudiante?->codigo ?? '',
                ($mesNombre[(int) $p->mes] ?? 'Mes ' . $p->mes) . ' ' . ($p->registration?->enrollment?->academicYear?->nombre ?? ''),
                $p->monto ?? $p->monto_esperado,
                $p->estado,
            ]);
            return $this->excelResponse('reporte_pagos_' . date('Ymd_His'), $headers, $rows);
        }

        $rowsHtml = $query->map(fn ($p) => '<tr><td>' . e($p->registration?->enrollment?->estudiante?->nombres . ' ' . $p->registration?->enrollment?->estudiante?->apellidos) . '</td><td>' . e(($mesNombre[(int) $p->mes] ?? 'Mes ' . $p->mes) . ' ' . ($p->registration?->enrollment?->academicYear?->nombre ?? '')) . '</td><td>' . e($p->monto ?? $p->monto_esperado) . '</td><td>' . e($p->estado) . '</td></tr>')->implode('');
        $body = '<table><thead><tr><th>Estudiante</th><th>Mensualidad</th><th>Monto</th><th>Estado</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table>';
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
