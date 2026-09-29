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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exportación e importación de plantillas Excel de evaluación por trimestre (HU-48).
 *
 * Formato de la plantilla (2 hojas):
 *  - Hoja "Criterios": metadatos (colegio, curso, materia, docente, trimestre) + los 4 criterios
 *    con sus actividades. El docente puede ver/ajustar las actividades aquí.
 *  - Hoja "Notas": matriz estudiantes (filas) x actividades (columnas). Cada columna lleva el
 *    código del criterio (SE/SA/HA/AU) y el nombre de la actividad. El docente llena las notas
 *    (0..porcentaje del criterio). Al re-subir, se actualizan las notas de las actividades
 *    existentes (las actividades se administran desde la web). Se rechazan notas fuera de rango.
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
    private const COLOR_BORDE = 'C9D9C9';        // verde grisáceo para bordes
    private const COLOR_TITULO = '003200';       // verde institucional para títulos
    private const COLOR_INSTITUCIONAL_CLARO = 'F6FAF6';

    /**
     * Inserta el logo institucional centrado horizontalmente en un bloque de columnas
     * (por defecto A:C) y con el alto indicado.
     */
    private function insertarLogo(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $alturaPx = 96,
        int $offsetY = 22,
        string $colIni = 'A',
        string $colFin = 'C'
    ): void {
        $path = public_path('images/logo-colegio-thumb.png');
        if (! is_file($path)) {
            return;
        }

        $size = @getimagesize($path);
        $ratio = ($size && $size[1] > 0) ? $size[0] / $size[1] : 1.975;
        $anchoPx = (int) round($alturaPx * $ratio);

        $drawing = new Drawing;
        $drawing->setPath($path);
        $drawing->setCoordinates($colIni.'1');
        $drawing->setResizeProportional(true);
        $drawing->setHeight($alturaPx);
        $drawing->setOffsetX(max(0, (int) round(($this->anchoColumnasPx($sheet, $colIni, $colFin) - $anchoPx) / 2)));
        $drawing->setOffsetY($offsetY);
        $drawing->setWorksheet($sheet);
    }

    /**
     * Ancho estimado en píxeles de un rango de columnas (para centrar el logo).
     */
    private function anchoColumnasPx(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $colIni, string $colFin): int
    {
        $total = 0;
        for ($c = Coordinate::columnIndexFromString($colIni); $c <= Coordinate::columnIndexFromString($colFin); $c++) {
            $ancho = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->getWidth();
            $total += (int) round($ancho * 7 + 5);
        }

        return $total;
    }

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
        $criterios = $this->criteriosOrdenados($plan);

        $spreadsheet = new Spreadsheet;

        // --- Hoja Criterios ---
        $sheetC = $spreadsheet->getActiveSheet();
        $sheetC->setTitle('Criterios');

        // Membrete institucional
        $sheetC->setCellValue('D1', 'UNIDAD EDUCATIVA SAN BERNARDO — TARIJA');
        $sheetC->mergeCells('D1:G1');
        $sheetC->setCellValue('D2', 'PLANILLA DE EVALUACIÓN POR TRIMESTRE');
        $sheetC->mergeCells('D2:G2');
        $sheetC->setCellValue('D3', 'Criterios y actividades');
        $sheetC->mergeCells('D3:G3');
        $sheetC->getStyle('D1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheetC->getStyle('D2')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheetC->getStyle('D3')->getFont()->setSize(9)->setItalic(true)->getColor()->setARGB('FF33412F');

        // Metadatos
        $meta = [
            ['Colegio', $this->colegio],
            ['Curso', $cs->course->nombre],
            ['Materia', $cs->subject->nombre],
            ['Docente', $cs->teacher?->nombreCompleto() ?? '—'],
            ['Trimestre', $plan->trimestre.'°'],
            ['Evaluación', $plan->nombre ?? ('TRIM '.$plan->trimestre)],
        ];
        $fila = 5;
        foreach ($meta as [$etiqueta, $valor]) {
            $sheetC->setCellValue('D'.$fila, $etiqueta);
            $sheetC->setCellValue('E'.$fila, $valor);
            $sheetC->mergeCells('E'.$fila.':G'.$fila);
            $fila++;
        }

        // Tarjeta de información
        $rangoInfoC = 'D5:G10';
        $sheetC->getStyle($rangoInfoC)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.self::COLOR_INSTITUCIONAL_CLARO);
        $sheetC->getStyle($rangoInfoC)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF'.self::COLOR_BORDE);
        $sheetC->getStyle('D5:D10')->getFont()->setBold(true)->getColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheetC->getStyle('D5:D10')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheetC->getStyle('E5:G10')->getFont()->getColor()->setARGB('FF213547');
        $sheetC->getStyle('E5:G10')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // Cabecera de la tabla
        $filaCab = 12;
        $cabecera = ['Criterio', 'Código', 'Porcentaje', 'Tipo', 'Actividad'];
        foreach ($cabecera as $i => $texto) {
            $sheetC->setCellValue($this->colLetter($i + 1).$filaCab, $texto);
        }
        $rangoCab = 'A'.$filaCab.':E'.$filaCab;
        $sheetC->getStyle($rangoCab)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheetC->getStyle($rangoCab)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheetC->getStyle($rangoCab)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheetC->getRowDimension($filaCab)->setRowHeight(22);

        $row = $filaCab + 1;
        foreach ($criterios as $criterion) {
            $color = self::COLORES_CRITERIO[$criterion->codigo] ?? ['fill' => 'E7E6E6', 'font' => '595959'];
            $inicioCrit = $row;

            if ($criterion->activities->isEmpty()) {
                $sheetC->fromArray([
                    $criterion->nombre, $criterion->codigo, $criterion->porcentaje, $criterion->tipo, '(sin actividades)',
                ], null, "A{$row}");
                $row++;
            } else {
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

            // Color por criterio en toda la fila
            $rangoCrit = 'A'.$inicioCrit.':E'.($row - 1);
            $sheetC->getStyle($rangoCrit)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$color['fill']);
            $sheetC->getStyle($rangoCrit)->getFont()->getColor()->setARGB('FF'.$color['font']);
            $sheetC->getStyle('B'.$inicioCrit.':D'.$inicioCrit)->getFont()->setBold(true)
                ->getColor()->setARGB('FF'.$color['font']);
            $sheetC->getStyle('B'.$inicioCrit.':D'.$inicioCrit)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $ultimaFilaC = $row - 1;

        // Bordes y anchos
        $sheetC->getStyle('A'.$filaCab.':E'.$ultimaFilaC)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF'.self::COLOR_BORDE);
        $sheetC->getColumnDimension('A')->setWidth(24);
        $sheetC->getColumnDimension('B')->setWidth(10);
        $sheetC->getColumnDimension('C')->setWidth(12);
        $sheetC->getColumnDimension('D')->setWidth(16);
        $sheetC->getColumnDimension('E')->setWidth(40);
        $sheetC->getSheetView()->setZoomScale(95);
        $this->insertarLogo($sheetC, 110, 45);

        // --- Hoja Notas (réplica de la planilla oficial) ---
        $sheetN = $spreadsheet->createSheet();
        $sheetN->setTitle('Notas');
        $this->construirHojaNotas($sheetN, $plan, $cs, $criterios, $this->estudiantes($cs), true);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * Centralizador con formato de planilla oficial (HU-58): misma hoja Notas
     * pero con valores calculados en vez de fórmulas (es reporte, no plantilla).
     */
    public function exportarCentralizador(EvaluationPlan $plan): string
    {
        $plan->load(['courseSubject.subject', 'courseSubject.course.grade', 'courseSubject.course.section', 'courseSubject.course.gestion', 'criteria.activities']);
        $cs = $plan->courseSubject;

        $spreadsheet = new Spreadsheet;
        $sheetN = $spreadsheet->getActiveSheet();
        $sheetN->setTitle('Centralizador');
        $this->construirHojaNotas($sheetN, $plan, $cs, $this->criteriosOrdenados($plan), $this->estudiantes($cs), false);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * Construye la hoja de matriz estudiantes x actividades + promedios.
     * Con $conFormulas=false escribe los valores calculados (centralizador).
     */
    private function construirHojaNotas(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheetN,
        EvaluationPlan $plan,
        CourseSubject $cs,
        Collection $criterios,
        Collection $estudiantes,
        bool $conFormulas
    ): void {
        $service = app(EvaluationService::class);

        // Metadatos (filas 1-7): membrete institucional + DOCENTE/CURSO/MATERIA/TRIMESTRE
        $sheetN->setCellValue('D1', 'UNIDAD EDUCATIVA SAN BERNARDO — TARIJA');
        $sheetN->mergeCells('D1:H1');
        $sheetN->setCellValue('D2', $conFormulas
            ? 'PLANILLA DE EVALUACIÓN POR CRITERIOS — TRIMESTRE ' . $plan->trimestre
            : 'CENTRALIZADOR DE CALIFICACIONES POR TRIMESTRE ' . $plan->trimestre . '°');
        $sheetN->mergeCells('D2:H2');
        $sheetN->setCellValue('D3', 'Registro de notas por criterio (SE/SA/HA/AU)  ·  Gestión '.($cs->course->gestion->nombre ?? now()->year));
        $sheetN->mergeCells('D3:H3');
        $sheetN->setCellValue('D4', 'DOCENTE');
        $sheetN->mergeCells('D4:E4');
        $sheetN->setCellValue('F4', $cs->teacher?->nombreCompleto() ?? '—');
        $sheetN->mergeCells('F4:H4');
        $sheetN->setCellValue('D5', 'CURSO');
        $sheetN->mergeCells('D5:E5');
        $sheetN->setCellValue('F5', $cs->course->nombre);
        $sheetN->mergeCells('F5:H5');
        $sheetN->setCellValue('D6', 'MATERIA');
        $sheetN->mergeCells('D6:E6');
        $sheetN->setCellValue('F6', $cs->subject->nombre);
        $sheetN->mergeCells('F6:H6');
        $sheetN->setCellValue('D7', 'TRIMESTRE');
        $sheetN->mergeCells('D7:E7');
        $sheetN->setCellValue('F7', $plan->trimestre.'°');
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
        $rangosCriterio = [];
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
            $rangosCriterio[] = [
                'codigo' => $criterion->codigo,
                'inicio' => $inicio,
                'fin' => $col - 1,
                'prom' => $promCols[$criterion->codigo],
            ];
        }
        // Columna final: promedio evaluación (merge filas 8:9)
        $colTotal = $col;
        $sheetN->setCellValue($this->colLetter($colTotal).$filaC, 'PROMEDIO EVALUACIÓN '.$plan->trimestre);
        $sheetN->mergeCells($this->colLetter($colTotal).$filaC.':'.$this->colLetter($colTotal).$filaA);
        $ultimaCol = $colTotal;

        // Filas de estudiantes: con FÓRMULAS (plantilla) o valores (centralizador)
        foreach ($estudiantes as $i => $student) {
            $sheetN->setCellValue('A'.$filaDatos, $i + 1);
            $sheetN->setCellValue('B'.$filaDatos, $student->apellidos.' '.$student->nombres);
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
                if ($nAct > 0) {
                    if ($conFormulas) {
                        // Fórmula promedio del criterio: =IFERROR(ROUND(AVERAGE(rango),0),"")
                        // El rango cubre todas las actividades del criterio (los vacíos los ignora AVERAGE)
                        $rango = $this->colLetter($inicio).$filaDatos.':'.$this->colLetter($inicio + $nAct - 1).$filaDatos;
                        $sheetN->setCellValue(
                            $this->colLetter($promCols[$criterion->codigo]).$filaDatos,
                            "=IFERROR(ROUND(AVERAGE($rango),0),\"\")"
                        );
                    } else {
                        $notaCrit = $service->notaCriterio($criterion, $student);
                        if ($notaCrit !== null) {
                            $sheetN->setCellValue($this->colLetter($promCols[$criterion->codigo]).$filaDatos, $notaCrit);
                        }
                    }
                }
                $col++; // pasa la columna de promedio
            }
            if ($conFormulas) {
                // Fórmula total: =SUM de los promedios de los criterios
                $refs = collect($criterios)->map(fn ($c) => $this->colLetter($promCols[$c->codigo]).$filaDatos)->toArray();
                $sheetN->setCellValue($this->colLetter($colTotal).$filaDatos, '=SUM('.implode(',', $refs).')');
            } else {
                $notaTrim = $service->notaTrimestre($plan, $student);
                if ($notaTrim !== null) {
                    $sheetN->setCellValue($this->colLetter($colTotal).$filaDatos, $notaTrim);
                }
            }
            $filaDatos++;
        }
        $ultimaFila = $filaDatos - 1;

        $this->estilizarHojaNotas($sheetN, $ultimaCol, $filaC, $filaA, $ultimaFila, $criterios, $promCols, $colTotal, $rangosCriterio);
        $this->insertarLogo($sheetN, 110, 15);
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
        int $colTotal,
        array $rangosCriterio = []
    ): void {
        $letraTotal = $this->colLetter($colTotal);
        $letraUlt = $this->colLetter($ultimaCol);

        // Fuente base
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$ultimaFila)->getFont()->setName('Calibri')->setSize(10);

        // Metadatos: membrete institucional + etiquetas en negrita verde
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheet->getStyle('D3')->getFont()->setSize(9)->setItalic(true)->getColor()->setARGB('FF33412F');
        $sheet->getStyle('D1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Tarjeta de información: DOCENTE / CURSO / MATERIA / TRIMESTRE
        $rangoInfo = 'D4:H7';
        $sheet->getStyle($rangoInfo)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.self::COLOR_INSTITUCIONAL_CLARO);
        $sheet->getStyle($rangoInfo)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF'.self::COLOR_BORDE);
        $sheet->getStyle('D4:E7')->getFont()->setBold(true)->getColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheet->getStyle('D4:E7')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('F4:H7')->getFont()->getColor()->setARGB('FF213547');
        $sheet->getStyle('F4:H7')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // Cabecera base (filas 8 y 9): institucional
        $rangoHeader = 'A'.$filaC.':'.$letraUlt.$filaA;
        $sheet->getStyle($rangoHeader)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rangoHeader)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheet->getStyle($rangoHeader)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension($filaC)->setRowHeight(20);
        $sheet->getRowDimension($filaA)->setRowHeight(30);

        // Color por criterio en su bloque de columnas (fila 8 y 9), como en el sistema
        foreach ($rangosCriterio as $r) {
            $color = self::COLORES_CRITERIO[$r['codigo']] ?? ['fill' => 'E7E6E6', 'font' => '595959'];
            $letraIni = $this->colLetter($r['inicio']);
            $letraFin = $this->colLetter($r['fin']);
            $bloque = $letraIni.$filaC.':'.$letraFin.$filaA;

            $sheet->getStyle($bloque)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$color['fill']);
            $sheet->getStyle($bloque)->getFont()->getColor()->setARGB('FF'.$color['font']);

            // Columna PROMEDIO del criterio resaltada
            $letraProm = $this->colLetter($r['prom']);
            $sheet->getStyle($letraProm.$filaC.':'.$letraProm.$filaA)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$color['font']);
            $sheet->getStyle($letraProm.$filaC.':'.$letraProm.$filaA)->getFont()
                ->getColor()->setARGB('FFFFFFFF');
        }

        // Columna PROMEDIO EVALUACIÓN con color institucional fuerte
        $sheet->getStyle($letraTotal.$filaC.':'.$letraTotal.$filaA)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.self::COLOR_TITULO);
        $sheet->getStyle($letraTotal.$filaC.':'.$letraTotal.$filaA)->getFont()
            ->setBold(true)->getColor()->setARGB('FFFFFFFF');

        // Bordes finos visibles en toda la tabla
        $sheet->getStyle('A'.$filaC.':'.$letraUlt.$ultimaFila)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF'.self::COLOR_BORDE);

        // Alineación: nombres a la izquierda, notas y promedios centrados
        $sheet->getStyle('B'.$filaC.':B'.$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A'.($filaA + 1).':'.$letraUlt.$ultimaFila)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Filas alternas tenues para legibilidad
        for ($f = $filaA + 1; $f <= $ultimaFila; $f++) {
            if (($f - $filaA) % 2 === 0) {
                $sheet->getStyle('A'.$f.':'.$letraUlt.$f)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.self::COLOR_INSTITUCIONAL_CLARO);
            }
        }

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

        // Hoja Notas: firma de la plantilla del sistema. Sin ella, el archivo
        // no es una plantilla válida (ej. planilla oficial u otro Excel).
        $sheetN = $spreadsheet->getSheetByName('Notas');
        if (! $sheetN) {
            return [
                'estudiantes' => 0,
                'notas' => 0,
                'errores' => [],
                'formato_invalido' => true,
            ];
        }
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
                        $nota = (float) $valor;
                        if ($nota < 0 || $nota > $criterion->porcentaje) {
                            $errores[] = "Fila $fila: nota $nota fuera de rango (0-{$criterion->porcentaje}) en «{$activity->nombre}»";
                        } else {
                            $service->registrarNota($activity, $student, $nota);
                            $notasRegistradas++;
                        }
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
            'formato_invalido' => false,
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
