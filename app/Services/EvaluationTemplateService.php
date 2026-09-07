<?php

namespace App\Services;

use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exportación e importación de plantillas Excel de evaluación por trimestre (HU-48).
 *
 * Formato de la plantilla (2 hojas):
 *  - Hoja "Criterios": metadatos (colegio, curso, materia, docente, trimestre) + los 4 criterios
 *    con sus actividades. El docente puede ver/ajustar las actividades aquí.
 *  - Hoja "Notas": matriz estudiantes (filas) x actividades (columnas). Cada columna lleva el
 *    código del criterio (SE/SA/HA/AU) y el nombre de la actividad. El docente llena las notas
 *    (0..porcentaje del criterio). Al re-subir, se actualizan actividades y notas.
 */
class EvaluationTemplateService
{
    /** Nombre del colegio. */
    private string $colegio = 'Colegio San Bernardo de Tarija';

    /** Orden lógico de los criterios (SE, SA, HA, AU). */
    private const ORDEN_CRITERIOS = ['SE' => 1, 'SA' => 2, 'HA' => 3, 'AU' => 4];

    /**
     * Paleta de colores por criterio (relleno de encabezado + texto), similar a la colorimetría del sistema.
     * SE=azul, SA=ámbar, HA=verde, AU=gris.
     */
    private const COLORES_CRITERIO = [
        'SE' => ['fill' => 'DCE6F1', 'font' => '1F4E79'], // azul claro / azul oscuro
        'SA' => ['fill' => 'FCE8D1', 'font' => '9C4A00'], // ámbar claro / ámbar oscuro
        'HA' => ['fill' => 'D9EAD3', 'font' => '1E6B34'], // verde claro / verde oscuro
        'AU' => ['fill' => 'E7E6E6', 'font' => '595959'], // gris claro / gris oscuro
    ];

    private const COLOR_FILA_ALTERNA = 'F7F9FC'; // azul muy tenue para filas alternas
    private const COLOR_BORDE = 'B7C4D6';        // gris azulado para bordes
    private const COLOR_TITULO = '2E9E3A';        // verde institucional para títulos

    /**
     * Criterios de un plan ordenados lógicamente (SE, SA, HA, AU),
     * con sus actividades ordenadas por orden/id.
     */
    private function criteriosOrdenados(EvaluationPlan $plan): Collection
    {
        return $plan->criteria
            ->sortBy(fn ($c) => self::ORDEN_CRITERIOS[$c->codigo] ?? 99)
            ->map(function ($criterion) {
                $criterion->setRelation('activities', $criterion->activities->sortBy('orden')->values());

                return $criterion;
            })
            ->values();
    }

    /**
     * Genera el archivo Excel (binario) de la plantilla para un plan.
     */
    public function exportar(EvaluationPlan $plan): string
    {
        $plan->load(['courseSubject.subject', 'courseSubject.course.grade', 'courseSubject.course.section', 'criteria.activities']);
        $cs = $plan->courseSubject;
        $service = app(EvaluationService::class);
        $criterios = $this->criteriosOrdenados($plan);

        $spreadsheet = new Spreadsheet;

        // --- Hoja Criterios ---
        $sheetC = $spreadsheet->getActiveSheet();
        $sheetC->setTitle('Criterios');
        $sheetC->fromArray([
            ['Colegio', $this->colegio],
            ['Curso', $cs->course->nombre],
            ['Materia', $cs->subject->nombre],
            ['Docente', $cs->teacher?->nombreCompleto() ?? ''],
            ['Trimestre', $plan->trimestre],
            ['Evaluación', $plan->nombre ?? ('TRIM '.$plan->trimestre)],
            [],
            ['Criterio', 'Código', 'Porcentaje', 'Tipo', 'Actividad'],
        ], null, 'A1');

        $row = 9;
        foreach ($criterios as $criterion) {
            if ($criterion->activities->isEmpty()) {
                $sheetC->fromArray([
                    $criterion->nombre, $criterion->codigo, $criterion->porcentaje, $criterion->tipo, '',
                ], null, "A{$row}");
                $row++;

                continue;
            }
            foreach ($criterion->activities as $i => $activity) {
                $sheetC->fromArray([
                    $i === 0 ? $criterion->nombre : '',
                    $i === 0 ? $criterion->codigo : '',
                    $i === 0 ? $criterion->porcentaje : '',
                    $i === 0 ? $criterion->tipo : '',
                    $activity->nombre,
                ], null, "A{$row}");
                $row++;
            }
        }
        $sheetC->getColumnDimension('A')->setWidth(24);
        $sheetC->getColumnDimension('B')->setWidth(10);
        $sheetC->getColumnDimension('C')->setWidth(12);
        $sheetC->getColumnDimension('D')->setWidth(14);
        $sheetC->getColumnDimension('E')->setWidth(40);
        $sheetC->getStyle('A8:E8')->getFont()->setBold(true);
        $sheetC->getStyle('A8:E8')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_TITULO);
        $sheetC->getStyle('A8:E8')->getFont()->getColor()->setARGB('FFFFFF');

        // --- Hoja Notas (réplica de la planilla oficial) ---
        $sheetN = $spreadsheet->createSheet();
        $sheetN->setTitle('Notas');
        $estudiantes = $this->estudiantes($cs);

        // Metadatos (filas 1-7): título en D1, DOCENTE/CURSO/MATERIA/TRIMESTRE en filas 4-7
        $sheetN->setCellValue('D1', $this->colegio.' - '.$plan->nombre);
        $sheetN->mergeCells('D1:H3');
        $sheetN->setCellValue('D4', 'DOCENTE:');
        $sheetN->mergeCells('D4:E4');
        $sheetN->setCellValue('F4', $cs->teacher?->nombreCompleto() ?? '');
        $sheetN->mergeCells('F4:H4');
        $sheetN->setCellValue('D5', 'CURSO:');
        $sheetN->mergeCells('D5:E5');
        $sheetN->setCellValue('F5', $cs->course->nombre);
        $sheetN->mergeCells('F5:H5');
        $sheetN->setCellValue('D6', 'MATERIA:');
        $sheetN->mergeCells('D6:E6');
        $sheetN->setCellValue('F6', $cs->subject->nombre);
        $sheetN->mergeCells('F6:H6');
        $sheetN->setCellValue('D7', 'TRIMESTRE:');
        $sheetN->mergeCells('D7:E7');
        $sheetN->setCellValue('F7', $plan->trimestre);
        $sheetN->mergeCells('F7:H7');

        // Encabezados de columna (filas 8 y 9)
        $filaC = 8;   // fila de criterios (SE, SA, HA, AU)
        $filaA = 9;   // fila de actividades + PROMEDIO
        $filaDatos = 10;

        // Columna A=N.º, B=ESTUDIANTES (merge B:C), C=separador
        $sheetN->setCellValue('A'.$filaC, 'No');
        $sheetN->mergeCells('A'.$filaC.':A'.$filaA);
        $sheetN->setCellValue('B'.$filaC, 'ESTUDIANTES');
        $sheetN->mergeCells('B'.$filaC.':C'.$filaA);

        // Columnas de criterios (desde columna D = col 4)
        $col = 4;
        $promCols = [];
        foreach ($criterios as $criterion) {
            $inicio = $col;
            foreach ($criterion->activities as $activity) {
                $sheetN->setCellValue($this->colLetter($col).$filaA, $activity->nombre);
                $headerMap[$col] = ['activity' => $activity, 'codigo' => $criterion->codigo];
                $col++;
            }
            // Columna de promedio del criterio
            $promCols[$criterion->codigo] = $col;
            $sheetN->setCellValue($this->colLetter($col).$filaA, 'PROMEDIO');
            $col++;
            // Merge del encabezado del criterio en la fila 8
            $fin = $col - 1;
            if ($inicio <= $fin) {
                $sheetN->mergeCells($this->colLetter($inicio).$filaC.':'.$this->colLetter($fin).$filaC);
                $sheetN->setCellValue($this->colLetter($inicio).$filaC, $criterion->codigo.' ('.$criterion->porcentaje.'%)');
            }
        }
        // Columna final: promedio evaluación (merge filas 8:9)
        $colTotal = $col;
        $sheetN->setCellValue($this->colLetter($colTotal).$filaC, 'PROMEDIO EVALUACIÓN '.$plan->trimestre);
        $sheetN->mergeCells($this->colLetter($colTotal).$filaC.':'.$this->colLetter($colTotal).$filaA);
        $ultimaCol = $colTotal;

        // Filas de estudiantes con FÓRMULAS (réplica exacta de la planilla oficial)
        foreach ($estudiantes as $i => $student) {
            $sheetN->setCellValue('A'.$filaDatos, $i + 1);
            $sheetN->setCellValue('B'.$filaDatos, '. '.$student->apellidos.' '.$student->nombres);
            $sheetN->mergeCells('B'.$filaDatos.':C'.$filaDatos);

            $col = 4;
            foreach ($criterios as $criterion) {
                $inicio = $col;
                $nAct = $criterion->activities->count();
                foreach ($criterion->activities as $activity) {
                    $nota = $activity->notaDe($student);
                    if ($nota !== null) {
                        $sheetN->setCellValue($this->colLetter($col).$filaDatos, $nota);
                    }
                    $col++;
                }
                // Fórmula promedio del criterio: =IFERROR(ROUND(AVERAGE(rango),0),"")
                // El rango cubre todas las actividades del criterio (los vacíos los ignora AVERAGE)
                if ($nAct > 0) {
                    $rango = $this->colLetter($inicio).$filaDatos.':'.$this->colLetter($inicio + $nAct - 1).$filaDatos;
                    $sheetN->setCellValue(
                        $this->colLetter($promCols[$criterion->codigo]).$filaDatos,
                        "=IFERROR(ROUND(AVERAGE($rango),0),\"\")"
                    );
                }
                $col++; // pasa la columna de promedio
            }
            // Fórmula total: =SUM de los promedios de los criterios
            $refs = collect($criterios)->map(fn ($c) => $this->colLetter($promCols[$c->codigo]).$filaDatos)->toArray();
            $sheetN->setCellValue($this->colLetter($colTotal).$filaDatos, '=SUM('.implode(',', $refs).')');
            $filaDatos++;
        }
        $ultimaFila = $filaDatos - 1;

        $this->estilizarHojaNotas($sheetN, $ultimaCol, $filaC, $filaA, $ultimaFila, $criterios, $promCols, $colTotal);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * Aplica el estilo de la planilla oficial: cabecera gris, celdas PROMEDIO grises,
     * bordes finos visibles y anchos de columna adecuados.
     */
    private function estilizarHojaNotas(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $ultimaCol,
        int $filaC,
        int $filaA,
        int $ultimaFila,
        Collection $criterios,
        array $promCols,
        int $colTotal
    ): void {
        $letraTotal = $this->colLetter($colTotal);
        $letraUlt = $this->colLetter($ultimaCol);

        // Fuente base
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$ultimaFila)->getFont()->setName('Calibri')->setSize(10);

        // Metadatos: título centrado, etiquetas en negrita
        $sheet->getStyle('D1:H3')->getFont()->setBold(true)->setSize(13)->getColor()->setARGB(self::COLOR_TITULO);
        $sheet->getStyle('D1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D4:D7')->getFont()->setBold(true);

        // Cabecera (filas 8 y 9): fondo gris + negrita, como la planilla oficial
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$filaA)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$filaA)->getFont()->setBold(true);
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$filaA)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Bordes finos visibles en toda la tabla
        $thin = ['style' => Border::BORDER_THIN, 'color' => ['argb' => '000000']];
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$ultimaFila)->getBorders()->getAllBorders()->applyFromArray($thin);

        // Alineación: nombres a la izquierda, notas y promedios centrados
        $sheet->getStyle('B'.$filaC.':B'.$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A'.($filaA + 1).':'.$letraUlt.$ultimaFila)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Anchos de columna
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(6);
        for ($c = 4; $c <= $ultimaCol; $c++) {
            $ancho = in_array($c, $promCols, true) ? 10 : 12;
            $sheet->getColumnDimension($this->colLetter($c))->setWidth($ancho);
        }
        $sheet->getColumnDimension($letraTotal)->setWidth(14);

        // Congelar paneles (columna B + fila 9) y zoom
        $sheet->freezePane('D'.($filaA + 1));
        $sheet->getSheetView()->setZoomScale(90);
    }

    /**
     * Importa una plantilla subida y actualiza actividades + notas del plan.
     */
    public function importar(EvaluationPlan $plan, string $rutaArchivo): array
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($rutaArchivo);

        // Hoja Notas
        $sheetN = $spreadsheet->getSheetByName('Notas') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheetN->getHighestRow();
        $highestCol = $sheetN->getHighestColumn();

        $estudiantes = $this->estudiantes($plan->courseSubject);
        $estByNombre = [];
        foreach ($estudiantes as $st) {
            $estByNombre[mb_strtolower(trim($st->apellidos.' '.$st->nombres))] = $st;
        }

        $service = app(EvaluationService::class);
        $actualizadas = 0;
        $notasRegistradas = 0;
        $errores = [];

        // Los datos de estudiantes inician en la fila 10 (filas 8-9 = encabezados de criterio/actividad)
        for ($fila = 10; $fila <= $highestRow; $fila++) {
            $nombre = trim((string) $sheetN->getCell('B'.$fila)->getValue());
            $nombre = preg_replace('/^\.\s*/', '', $nombre);
            if ($nombre === '') {
                continue;
            }
            $student = $estByNombre[mb_strtolower($nombre)] ?? null;
            if (! $student) {
                $errores[] = "Fila $fila: estudiante no encontrado en el curso: $nombre";

                continue;
            }

            $col = 4; // columna D = primera actividad (A=N.º, B:C=estudiante)
            foreach ($this->criteriosOrdenados($plan) as $criterion) {
                foreach ($criterion->activities as $activity) {
                    $valor = $sheetN->getCell($this->colLetter($col).$fila)->getValue();
                    if (is_numeric($valor)) {
                        $service->registrarNota($activity, $student, (float) $valor);
                        $notasRegistradas++;
                    }
                    $col++;
                }
                $col++; // columna promedio (ignorar al importar)
            }
            $actualizadas++;
        }

        return [
            'estudiantes' => $actualizadas,
            'notas' => $notasRegistradas,
            'errores' => $errores,
        ];
    }

    private function estudiantes(CourseSubject $cs): Collection
    {
        return $cs->course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->orderBy('students.nombres')
            ->get();
    }

    private function colLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }
}
