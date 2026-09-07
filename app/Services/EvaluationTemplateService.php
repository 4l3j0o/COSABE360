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
        foreach ($this->criteriosOrdenados($plan) as $criterion) {
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

        // --- Hoja Notas ---
        $sheetN = $spreadsheet->createSheet();
        $sheetN->setTitle('Notas');
        $estudiantes = $this->estudiantes($cs);

        // Encabezado de título (fila 1) y de criterio/actividad (fila 2-3)
        $ultimaColTitle = $this->columnaDerechaTotal($plan);
        $sheetN->setCellValue('A1', 'COLEGIO SAN BERNARDO DE TARIJA — EVALUACIÓN POR TRIMESTRE');
        $sheetN->mergeCells('A1:' . $this->colLetter($ultimaColTitle) . '1');
        $sheetN->setCellValue('A2', $cs->course->nombre.' | '.$cs->subject->nombre.' | Docente: '.($cs->teacher?->nombreCompleto() ?? ''));
        $sheetN->mergeCells('A2:' . $this->colLetter($ultimaColTitle) . '2');

        $filaEncabezado = 4; // los títulos de columnas van en la fila 4
        $col = 1;
        $sheetN->setCellValue($this->colLetter($col).$filaEncabezado, 'N.º'); $col++;
        $sheetN->setCellValue($this->colLetter($col).$filaEncabezado, 'ESTUDIANTES'); $col++;

        $headerMap = [];
        $promCols = [];
        foreach ($this->criteriosOrdenados($plan) as $criterion) {
            $inicio = $col;
            foreach ($criterion->activities as $activity) {
                $sheetN->setCellValue($this->colLetter($col).$filaEncabezado, $activity->nombre);
                $headerMap[$col] = ['activity' => $activity, 'codigo' => $criterion->codigo];
                $col++;
            }
            // Columna de promedio del criterio
            $sheetN->setCellValue($this->colLetter($col).$filaEncabezado, 'PROM. '.$criterion->codigo);
            $promCols[$criterion->codigo] = $col;
            $col++;
        }
        $sheetN->setCellValue($this->colLetter($col).$filaEncabezado, 'NOTA TRIMESTRE');
        $colNotaTrim = $col;
        $ultimaCol = $col;

        // Segunda fila de encabezado: código del criterio (fila 5)
        $filaCodigo = $filaEncabezado + 1;
        foreach ($headerMap as $colIndex => $info) {
            $sheetN->setCellValue($this->colLetter($colIndex).$filaCodigo, $info['codigo'].' ('.$this->criterioPorCodigo($plan, $info['codigo'])->porcentaje.'%)');
        }
        foreach ($promCols as $codigo => $colIndex) {
            $sheetN->setCellValue($this->colLetter($colIndex).$filaCodigo, 'Promedio '.$codigo);
        }
        $sheetN->setCellValue($this->colLetter($colNotaTrim).$filaCodigo, 'TRIM '.$plan->trimestre);

        // Filas de estudiantes con FÓRMULAS
        $fila = $filaCodigo + 1;
        foreach ($estudiantes as $i => $student) {
            $sheetN->setCellValue($this->colLetter(1).$fila, $i + 1);
            $sheetN->setCellValue($this->colLetter(2).$fila, $student->apellidos.' '.$student->nombres);

            $col = 3;
            $criteriosNota = [];
            foreach ($this->criteriosOrdenados($plan) as $criterion) {
                $inicio = $col;
                $criteriosNota[$criterion->codigo] = [];
                foreach ($criterion->activities as $activity) {
                    $nota = $activity->notaDe($student);
                    if ($nota !== null) {
                        $sheetN->setCellValue($this->colLetter($col).$fila, $nota);
                        $criteriosNota[$criterion->codigo][] = $this->colLetter($col).$fila;
                    }
                    $col++;
                }
                // Fórmula de promedio del criterio (suma de actividades / total de actividades del criterio)
                $promCol = $this->colLetter($promCols[$criterion->codigo]).$fila;
                $actividadesCriterio = $criterion->activities->count();
                if ($actividadesCriterio > 0) {
                    $rango = $this->colLetter($inicio).$fila.':'.$this->colLetter($inicio + $actividadesCriterio - 1).$fila;
                    // promedio = suma / número total de actividades (los vacíos cuentan como 0)
                    $sheetN->setCellValue($promCol, "=ROUND(SUM($rango)/$actividadesCriterio,0)");
                }
                $col++;
            }

            // Fórmula de nota trimestre = SUMA de los promedios de los 4 criterios
            $criteriosCodigo = $this->criteriosOrdenados($plan)->pluck('codigo')->map(fn ($c) => $this->colLetter($promCols[$c]).$fila)->toArray();
            $sheetN->setCellValue($this->colLetter($colNotaTrim).$fila, '=ROUND(SUM('.implode(',', $criteriosCodigo).'),0)');

            $fila++;
        }
        $ultimaFila = $fila - 1;

        $this->estilizarHojaNotas($sheetN, $ultimaCol, $filaEncabezado, $filaCodigo, $ultimaFila, $plan, $promCols, $colNotaTrim);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * Aplica colores, bordes y anchos a la hoja Notas, estilo "planilla Excel" con
     * encabezado coloreado por criterio, filas alternas y líneas visibles.
     */
    private function estilizarHojaNotas(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $ultimaCol,
        int $filaEncabezado,
        int $filaCodigo,
        int $ultimaFila,
        EvaluationPlan $plan,
        array $promCols,
        int $colNotaTrim
    ): void {
        $rangoEncabezado = 'A'.$filaEncabezado.':'.$this->colLetter($ultimaCol).$filaEncabezado;
        $rangoCodigo = 'A'.$filaCodigo.':'.$this->colLetter($ultimaCol).$filaCodigo;
        $rangoDatos = 'A'.$filaEncabezado.':'.$this->colLetter($ultimaCol).$ultimaFila;

        // Fuente base
        $sheet->getStyle($rangoDatos)->getFont()->setName('Calibri')->setSize(10);

        // Título
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14)->getColor()->setARGB(self::COLOR_TITULO);
        $sheet->getStyle('A2')->getFont()->getColor()->setARGB('595959');

        // Colorear columnas por criterio (encabezado de nombre + código + promedio)
        foreach ($this->criteriosOrdenados($plan) as $criterion) {
            $color = self::COLORES_CRITERIO[$criterion->codigo] ?? ['fill' => 'F2F2F2', 'font' => '000000'];
            [$inicio, $fin] = $this->rangoColumnasCriterio($plan, $criterion->codigo);

            $rangoCol = $this->colLetter($inicio).$filaEncabezado.':'.$this->colLetter($fin).$filaCodigo;
            $sheet->getStyle($rangoCol)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color['fill']);
            $sheet->getStyle($rangoCol)->getFont()->getColor()->setARGB($color['font']);
            $sheet->getStyle($rangoCol)->getFont()->setBold(true);
            // Fondo del encabezado del criterio (código)
            $sheet->getStyle($this->colLetter($inicio).$filaCodigo.':'.$this->colLetter($fin).$filaCodigo)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color['fill']);
        }

        // Columna NOTA TRIMESTRE: encabezado destacado
        $sheet->getStyle($this->colLetter($colNotaTrim).$filaEncabezado.':'.$this->colLetter($colNotaTrim).$filaCodigo)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC'); // ámbar claro
        $sheet->getStyle($this->colLetter($colNotaTrim).$filaEncabezado.':'.$this->colLetter($colNotaTrim).$filaCodigo)
            ->getFont()->getColor()->setARGB('9C4A00');

        // Encabezado "Estudiante" / "N.º"
        $sheet->getStyle('A'.$filaEncabezado.':B'.$filaCodigo)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_TITULO);
        $sheet->getStyle('A'.$filaEncabezado.':B'.$filaCodigo)->getFont()->getColor()->setARGB('FFFFFF');

        // Bordes visibles en todo el rango
        $thin = ['style' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDE]];
        $medium = ['style' => Border::BORDER_MEDIUM, 'color' => ['argb' => '9AA7B8']];
        $sheet->getStyle($rangoDatos)->getBorders()->getAllBorders()->applyFromArray($thin);
        $sheet->getStyle('A'.$filaEncabezado.':'.$this->colLetter($ultimaCol).$filaCodigo)->getBorders()->getAllBorders()->applyFromArray($medium);

        // Filas alternas (bandas)
        for ($r = $filaCodigo + 2; $r <= $ultimaFila; $r += 2) {
            $sheet->getStyle('A'.$r.':'.$this->colLetter($ultimaCol).$r)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_FILA_ALTERNA);
        }

        // Centrar celdas numéricas
        $sheet->getStyle('A'.$filaEncabezado.':'.$this->colLetter($ultimaCol).$ultimaFila)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B'.$filaEncabezado.':B'.$ultimaFila)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle($rangoEncabezado)->getAlignment()->setWrapText(true);

        // Anchos de columna
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(36);
        for ($c = 3; $c <= $ultimaCol; $c++) {
            if ($c === $colNotaTrim) {
                $sheet->getColumnDimension($this->colLetter($c))->setWidth(14);
            } elseif (in_array($c, $promCols, true)) {
                $sheet->getColumnDimension($this->colLetter($c))->setWidth(12);
            } else {
                $sheet->getColumnDimension($this->colLetter($c))->setWidth(10);
            }
        }

        // Congelar paneles (filas de encabezado + columna de estudiante)
        $sheet->freezePane($this->colLetter(3).($filaCodigo + 1));
        $sheet->getSheetView()->setZoomScale(90);
    }

    /** Criterio por código (para el encabezado de ponderación). */
    private function criterioPorCodigo(EvaluationPlan $plan, string $codigo)
    {
        return $this->criteriosOrdenados($plan)->first(fn ($c) => $c->codigo === $codigo);
    }

    /** Columna derecha total de la hoja Notas (para el merge del título). */
    private function columnaDerechaTotal(EvaluationPlan $plan): int
    {
        $col = 3; // C (después de N.º y ESTUDIANTES)
        foreach ($this->criteriosOrdenados($plan) as $criterion) {
            $col += max(1, $criterion->activities->count()); // actividades
            $col++; // promedio del criterio
        }
        $col++; // nota trimestre

        return $col;
    }

    /** Rango [inicio, fin] de columnas (actividades + promedio) de un criterio en la hoja Notas. */
    private function rangoColumnasCriterio(EvaluationPlan $plan, string $codigo): array
    {
        $col = 3;
        foreach ($this->criteriosOrdenados($plan) as $criterion) {
            $inicio = $col;
            $fin = $col + max(1, $criterion->activities->count()); // actividades + promedio
            if ($criterion->codigo === $codigo) {
                return [$inicio, $fin];
            }
            $col = $fin + 1;
        }

        return [3, 3];
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

        // Los datos de estudiantes inician en la fila 6 (fila 4 = encabezado, fila 5 = código de criterio)
        for ($fila = 6; $fila <= $highestRow; $fila++) {
            $nombre = trim((string) $sheetN->getCell('B'.$fila)->getValue());
            if ($nombre === '') {
                continue;
            }
            $student = $estByNombre[mb_strtolower($nombre)] ?? null;
            if (! $student) {
                $errores[] = "Fila $fila: estudiante no encontrado en el curso: $nombre";

                continue;
            }

            $col = 3;
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
