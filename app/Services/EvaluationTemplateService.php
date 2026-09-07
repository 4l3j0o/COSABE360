<?php

namespace App\Services;

use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

        // --- Hoja Notas ---
        $sheetN = $spreadsheet->createSheet();
        $sheetN->setTitle('Notas');
        $estudiantes = $this->estudiantes($cs);

        // Encabezado
        $sheetN->setCellValue('A1', 'N.º');
        $sheetN->setCellValue('B1', 'ESTUDIANTES');
        $col = 3; // columna C
        $headerMap = []; // columna => ['codigo'=>..., 'id'=>activityId]

        foreach ($this->criteriosOrdenados($plan) as $criterion) {
            $inicio = $col;
            foreach ($criterion->activities as $activity) {
                $letra = $this->colLetter($col);
                $sheetN->setCellValue("{$letra}1", $activity->nombre);
                $sheetN->setCellValue("{$letra}2", $criterion->codigo.' ('.$criterion->porcentaje.'%)');
                $headerMap[$col] = ['activity' => $activity];
                $col++;
            }
            // Columna de promedio del criterio
            $letraProm = $this->colLetter($col);
            $sheetN->setCellValue("{$letraProm}1", 'PROMEDIO '.$criterion->codigo);
            $sheetN->setCellValue("{$letraProm}2", $criterion->codigo);
            $col++;
        }
        // Columna nota trimestre
        $letraTrim = $this->colLetter($col);
        $sheetN->setCellValue("{$letraTrim}1", 'NOTA TRIMESTRE');
        $sheetN->setCellValue("{$letraTrim}2", 'TRIM '.$plan->trimestre);
        $colNotaTrim = $col;

        $col = 3;
        foreach ($this->criteriosOrdenados($plan) as $criterion) {
            foreach ($criterion->activities as $activity) {
                $col++;
            }
            $col++; // promedio
        }
        $ultimaLetra = $this->colLetter($col);

        // Filas de estudiantes
        $fila = 3;
        $service = app(EvaluationService::class);
        foreach ($estudiantes as $i => $student) {
            $sheetN->setCellValue('A'.$fila, $i + 1);
            $sheetN->setCellValue('B'.$fila, $student->apellidos.' '.$student->nombres);

            $col = 3;
            $criteriosNota = [];
            foreach ($this->criteriosOrdenados($plan) as $criterion) {
                $notaCriterio = $service->notaCriterio($criterion, $student);
                $criteriosNota[$criterion->codigo] = $notaCriterio;
                foreach ($criterion->activities as $activity) {
                    $nota = $activity->notaDe($student);
                    if ($nota !== null) {
                        $sheetN->setCellValue($this->colLetter($col).$fila, $nota);
                    }
                    $col++;
                }
                // promedio criterio
                if ($notaCriterio !== null) {
                    $sheetN->setCellValue($this->colLetter($col).$fila, $notaCriterio);
                }
                $col++;
            }
            $notaTrim = $service->notaTrimestre($plan, $student);
            if ($notaTrim !== null) {
                $sheetN->setCellValue($this->colLetter($colNotaTrim).$fila, $notaTrim);
            }
            $fila++;
        }

        // Estilo encabezado
        $sheetN->getStyle('A1:'.$ultimaLetra.'2')->getFont()->setBold(true);
        $sheetN->getStyle('A1:'.$ultimaLetra.'2')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9EAD3');
        $sheetN->getStyle('A1:'.$ultimaLetra.'2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheetN->getColumnDimension('A')->setWidth(5);
        $sheetN->getColumnDimension('B')->setWidth(34);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
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

        // Para cada fila de estudiante
        for ($fila = 3; $fila <= $highestRow; $fila++) {
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
