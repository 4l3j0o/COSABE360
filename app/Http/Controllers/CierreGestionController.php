<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubject;
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
 * HU-52: Cierre de gestión — escala de valoración oficial (ED/DA/DO/DP) y promoción/retención.
 * Por curso, calcula el promedio anual por materia de cada estudiante, su valoración,
 * y determina si es promovido o retenido (retiene si una o más áreas < 51, R.M. 473/2021 Art. 51).
 */
class CierreGestionController extends Controller
{
    public function index(Request $request): View
    {
        $cursos = $this->cursosPermitidos($request->user());

        $curso = $request->query('course_id')
            ? Course::with(['grade', 'section', 'gestion'])->find($request->query('course_id'))
            : $cursos->first();

        if ($curso && ! $cursos->contains('id', $curso->id)) {
            abort(403);
        }

        $resultado = $curso ? $this->build($curso) : null;

        return view('cierre-gestion.index', compact('cursos', 'curso', 'resultado'));
    }

    public function export(Request $request): StreamedResponse
    {
        $curso = Course::with(['grade', 'section', 'gestion'])->findOrFail($request->query('course_id'));
        $resultado = $this->build($curso);

        $formato = $request->query('format', 'pdf');
        if ($formato === 'excel') {
            return $this->exportExcel($resultado);
        }

        return $this->exportPdf($resultado);
    }

    private function build(Course $course): array
    {
        $service = app(EvaluationService::class);
        $materias = CourseSubject::where('course_id', $course->id)->with('subject')->get();
        $students = $course->students()->where('students.estado', 'ACTIVO')->orderBy('apellidos')->get();

        $filas = $students->map(function (Student $student) use ($materias, $service) {
            $materiasNota = [];
            $reprobado = false;
            $conNota = 0;

            foreach ($materias as $cs) {
                $nota = $service->promedioAnual($cs, $student);
                if ($nota !== null) {
                    $conNota++;
                }
                if ($nota !== null && $nota < 51) {
                    $reprobado = true;
                }
                $materiasNota[] = [
                    'materia' => $cs->subject->nombre,
                    'nota' => $nota,
                    'valoracion' => $nota !== null ? EvaluationService::valoracion($nota) : null,
                ];
            }

            // Promedio general (todas las materias con nota)
            $notasValidas = collect($materiasNota)->pluck('nota')->filter();
            $promedioGeneral = $notasValidas->count() > 0 ? round($notasValidas->avg()) : null;

            return (object) [
                'student' => $student,
                'materias' => $materiasNota,
                'promedio' => $promedioGeneral,
                'promovido' => $reprobado ? false : ($conNota > 0 ? true : null),
                'reprobado_en' => $reprobado,
            ];
        });

        $promovidos = $filas->filter(fn ($f) => $f->promovido === true)->count();
        $retenidos = $filas->filter(fn ($f) => $f->promovido === false)->count();
        $sinNota = $filas->filter(fn ($f) => $f->promovido === null)->count();

        return [
            'curso' => $course,
            'filas' => $filas,
            'resumen' => ['promovidos' => $promovidos, 'retenidos' => $retenidos, 'sin_nota' => $sinNota, 'total' => $filas->count()],
        ];
    }

    private function cursosPermitidos($user): Collection
    {
        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            return Course::with(['grade', 'section'])->orderBy('nombre')->get();
        }

        return collect();
    }

    private function exportPdf(array $resultado): StreamedResponse
    {
        $pdf = Pdf::loadView('cierre-gestion.pdf', compact('resultado'))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'cierre-gestion_'.now()->format('Ymd').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function exportExcel(array $resultado): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cierre');

        $curso = $resultado['curso'];
        $sheet->setCellValue('A1', 'Cierre de Gestión - '.$curso->nombre);

        $sheet->setCellValue('A3', 'N.º');
        $sheet->setCellValue('B3', 'Estudiante');
        $col = 3;
        foreach ($resultado['filas']->first()?->materias ?? [] as $m) {
            $sheet->setCellValue($this->colLetter($col).'3', $m['materia']);
            $col++;
        }
        $sheet->setCellValue($this->colLetter($col).'3', 'Promedio');
        $sheet->setCellValue($this->colLetter($col + 1).'3', 'Estado');
        $ultimaLetra = $this->colLetter($col + 1);

        $fila = 4;
        foreach ($resultado['filas'] as $i => $f) {
            $sheet->setCellValue('A'.$fila, $i + 1);
            $sheet->setCellValue('B'.$fila, $f->student->apellidos.' '.$f->student->nombres);
            $col = 3;
            foreach ($f->materias as $m) {
                $sheet->setCellValue($this->colLetter($col).$fila, $m['nota'] ?? '');
                $col++;
            }
            $sheet->setCellValue($this->colLetter($col).$fila, $f->promedio ?? '');
            $sheet->setCellValue($this->colLetter($col + 1).$fila, $f->promovido === null ? '' : ($f->promovido ? 'Promovido' : 'Retenido'));
            $fila++;
        }
        $sheet->getStyle('A3:'.$ultimaLetra.'3')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return response()->streamDownload(
            fn () => print (ob_get_clean()),
            'cierre-gestion_'.now()->format('Ymd').'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function colLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }
}
