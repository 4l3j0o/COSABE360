<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\User;
use App\Services\RankingPromediosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ranking de mejores promedios por ámbito:
 * global, nivel, curso/grado y paralelo.
 *
 * Alcance:
 *  - Admin/Director/Secretaría: todo.
 *  - Docente: sus cursos/materias; si es tutor, además ve el promedio
 *    general de su paralelo y puede consultar cualquier materia de ese
 *    paralelo.
 */
class RankingPromediosController extends Controller
{
    private const SCOPES = ['global', 'nivel', 'grado', 'paralelo'];

    private const PERIODOS = [
        'anual' => 'Promedio anual',
        '1' => '1er trimestre',
        '2' => '2do trimestre',
        '3' => '3er trimestre',
    ];

    private const LIMITES = [10, 25, 50, 100];

    public function index(Request $request): View
    {
        $contexto = $this->contexto($request->user());
        $filtros = $this->resolverFiltros($request, $contexto);
        $resultado = $this->resultado($contexto, $filtros, $filtros['limite']);

        return view('ranking-promedios.index', [
            'filtros' => $filtros,
            'resultado' => $resultado,
            'niveles' => $contexto['niveles'],
            'grados' => $contexto['grados'],
            'secciones' => $contexto['sections'],
            'materias' => $filtros['materias'],
            'periodos' => self::PERIODOS,
            'generalDisponible' => $contexto['generalDisponible'],
            'esSupervisor' => $contexto['esSupervisor'],
            'scopeLabel' => $this->scopeLabel($contexto, $filtros),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $contexto = $this->contexto($request->user());
        $filtros = $this->resolverFiltros($request, $contexto);
        $resultado = $this->resultado($contexto, $filtros, null);
        $filtros['limite'] = null;

        if ($request->query('format') === 'excel') {
            return $this->exportExcel($resultado, $filtros);
        }

        $logoPath = public_path('images/escudo colegio.png');
        $logo = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('ranking-promedios.pdf', compact('resultado', 'filtros', 'logo'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'ranking-promedios_'.now()->format('Ymd').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function contexto(User $user): array
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->orderBy('id')->first();

        $base = Section::with(['grade.level', 'gestion'])
            ->where('estado', 'ACTIVO')
            ->when($gestion, fn ($query) => $query->where('academic_year_id', $gestion->id))
            ->ordenadoPorNivel();

        if ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría'])) {
            $sections = $base->get();

            return $this->armarContexto($sections, collect(), null, true);
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            $teacherId = (int) $user->teacher->id;

            $tutorSections = (clone $base)->where('teacher_id', $teacherId)->get();
            $assignedIds = SectionSubject::where('teacher_id', $teacherId)
                ->pluck('section_id')
                ->unique()
                ->values();
            $assignedSections = (clone $base)->whereIn('id', $assignedIds)->get();

            $sections = Section::ordenarPorNivel(
                $tutorSections->concat($assignedSections)->unique('id')->values()
            );

            return $this->armarContexto(
                $sections,
                $tutorSections->pluck('id')->map(fn ($id) => (int) $id),
                $teacherId,
                false
            );
        }

        abort(403, 'No tiene acceso al ranking de promedios.');
    }

    /**
     * @param  Collection<int, Section>  $sections
     * @param  Collection<int, int>  $tutorIds
     * @return array<string, mixed>
     */
    private function armarContexto(
        Collection $sections,
        Collection $tutorIds,
        ?int $teacherId,
        bool $esSupervisor
    ): array {
        $sectionSubjects = $sections->isEmpty()
            ? collect()
            : SectionSubject::with(['subject', 'section.grade.level'])
                ->whereIn('section_id', $sections->pluck('id'))
                ->get();

        $niveles = $sections
            ->map(fn (Section $section) => $section->grade?->level)
            ->filter()
            ->unique('id')
            ->sortBy('orden')
            ->values();

        $grados = $sections
            ->map(fn (Section $section) => $section->grade)
            ->filter()
            ->unique('id')
            ->sortBy(fn ($grade) => (int) ($grade->level?->orden ?? 999) * 100 + (int) $grade->orden)
            ->values();

        return [
            'sections' => $sections,
            'tutorIds' => $tutorIds->map(fn ($id) => (int) $id),
            'teacherId' => $teacherId,
            'esSupervisor' => $esSupervisor,
            'generalDisponible' => $esSupervisor || $tutorIds->isNotEmpty(),
            'sectionSubjects' => $sectionSubjects,
            'niveles' => $niveles,
            'grados' => $grados,
        ];
    }

    /**
     * @param  array<string, mixed>  $contexto
     * @return array<string, mixed>
     */
    private function resolverFiltros(Request $request, array $contexto): array
    {
        $defaultScope = (! $contexto['esSupervisor'] && $contexto['tutorIds']->isNotEmpty())
            ? 'paralelo'
            : 'global';

        $scope = (string) $request->query('scope', $defaultScope);
        if (! in_array($scope, self::SCOPES, true)) {
            $scope = $defaultScope;
        }

        $periodo = (string) $request->query('periodo', 'anual');
        if (! array_key_exists($periodo, self::PERIODOS)) {
            $periodo = 'anual';
        }

        $limiteSolicitado = $request->query('limite', '50');
        $limite = $limiteSolicitado === 'todos'
            ? null
            : (in_array((int) $limiteSolicitado, self::LIMITES, true) ? (int) $limiteSolicitado : 50);

        $niveles = $contexto['niveles'];
        $grados = $contexto['grados'];
        $secciones = $contexto['sections'];

        $nivelId = $this->idPermitido($request->query('nivel_id'), $niveles->pluck('id'));
        $gradoId = $this->idPermitido($request->query('grado_id'), $grados->pluck('id'));
        $sectionId = $this->idPermitido($request->query('section_id'), $secciones->pluck('id'));

        if ($scope === 'nivel' && ! $nivelId) {
            $nivelId = (int) ($niveles->first()->id ?? 0) ?: null;
        }
        if ($scope === 'grado' && ! $gradoId) {
            $gradoId = (int) ($grados->first()->id ?? 0) ?: null;
        }
        if ($scope === 'paralelo' && ! $sectionId) {
            $candidatas = (! $contexto['esSupervisor'] && $contexto['tutorIds']->isNotEmpty())
                ? $secciones->whereIn('id', $contexto['tutorIds'])
                : $secciones;
            $sectionId = (int) ($candidatas->first()->id ?? 0) ?: null;
        }

        $seccionesFiltradas = $secciones;

        if ($scope === 'nivel') {
            if (! $nivelId) {
                abort(403, 'No hay niveles disponibles.');
            }
            $seccionesFiltradas = $seccionesFiltradas
                ->filter(fn (Section $section) => (int) $section->grade?->level_id === $nivelId)
                ->values();
        } elseif ($scope === 'grado') {
            if (! $gradoId) {
                abort(403, 'No hay cursos disponibles.');
            }
            $seccionesFiltradas = $seccionesFiltradas
                ->filter(fn (Section $section) => (int) $section->grade_id === $gradoId)
                ->values();
        } elseif ($scope === 'paralelo') {
            if (! $sectionId) {
                abort(403, 'No hay paralelos disponibles.');
            }
            $seccionesFiltradas = $seccionesFiltradas
                ->filter(fn (Section $section) => (int) $section->id === $sectionId)
                ->values();
        }

        $materias = $this->materiasPermitidas(
            $contexto,
            $contexto['sectionSubjects']
        );

        $subjectId = $request->query('subject_id');
        $subjectId = ($subjectId === null || $subjectId === '') ? null : (int) $subjectId;

        if ($subjectId !== null && ! $materias->contains('id', $subjectId)) {
            abort(403, 'No tiene acceso a esa materia.');
        }

        if ($subjectId === null && ! $contexto['generalDisponible']) {
            $subjectId = (int) ($materias->first()->id ?? 0) ?: null;
        }

        if ($subjectId === null && ! $contexto['esSupervisor']) {
            // El promedio general de un docente solo incluye sus paralelos
            // donde es tutor; no puede mezclar cursos donde solo dicta.
            $seccionesFiltradas = $seccionesFiltradas
                ->whereIn('id', $contexto['tutorIds'])
                ->values();
        }

        if ($subjectId !== null) {
            $seccionesConMateria = $contexto['sectionSubjects']
                ->where('subject_id', $subjectId)
                ->filter(fn (SectionSubject $cs) => $contexto['esSupervisor']
                    || $contexto['tutorIds']->contains((int) $cs->section_id)
                    || (int) $cs->teacher_id === $contexto['teacherId'])
                ->pluck('section_id')
                ->map(fn ($id) => (int) $id)
                ->unique();

            $seccionesFiltradas = $seccionesFiltradas
                ->whereIn('id', $seccionesConMateria)
                ->values();
        }

        $materia = $subjectId !== null
            ? $materias->firstWhere('id', $subjectId)
            : null;

        return [
            'scope' => $scope,
            'periodo' => $periodo,
            'limite' => $limite,
            'nivel_id' => $nivelId,
            'grado_id' => $gradoId,
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'materias' => $materias,
            'materia' => $materia,
            'periodo_label' => self::PERIODOS[$periodo],
            'secciones_filtradas' => $seccionesFiltradas,
            'general_disponible' => $contexto['generalDisponible'],
        ];
    }

    private function idPermitido(mixed $valor, Collection $permitidos): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $id = (int) $valor;

        return $permitidos->contains($id) ? $id : null;
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    private function materiasPermitidas(array $contexto, Collection $sectionSubjects): Collection
    {
        return $sectionSubjects
            ->filter(fn (SectionSubject $cs) => $contexto['esSupervisor']
                || $contexto['tutorIds']->contains((int) $cs->section_id)
                || (int) $cs->teacher_id === $contexto['teacherId'])
            ->map(fn (SectionSubject $cs) => $cs->subject)
            ->filter()
            ->unique('id')
            ->sortBy('nombre')
            ->values();
    }

    /**
     * @param  array<string, mixed>  $contexto
     * @param  array<string, mixed>  $filtros
     */
    private function resultado(array $contexto, array $filtros, ?int $limite): array
    {
        $resultado = app(RankingPromediosService::class)->calcular(
            $filtros['secciones_filtradas'],
            $filtros['subject_id'],
            $filtros['periodo'],
            $limite
        );

        $resultado['materia'] = $filtros['materia'];
        $resultado['periodo_label'] = $filtros['periodo_label'];
        $resultado['scope_label'] = $this->scopeLabel($contexto, $filtros);
        $resultado['secciones'] = $filtros['secciones_filtradas'];

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $contexto
     * @param  array<string, mixed>  $filtros
     */
    private function scopeLabel(array $contexto, array $filtros): string
    {
        return match ($filtros['scope']) {
            'nivel' => $contexto['niveles']->firstWhere('id', $filtros['nivel_id'])?->nombre ?? 'Nivel',
            'grado' => $contexto['grados']->firstWhere('id', $filtros['grado_id'])?->nombre ?? 'Curso',
            'paralelo' => $contexto['sections']
                ->firstWhere('id', $filtros['section_id'])?->nombreCompleto() ?? 'Paralelo',
            default => ! $contexto['esSupervisor'] && $filtros['subject_id'] === null
                ? 'Mis paralelos como tutor'
                : 'Global',
        };
    }

    /**
     * @param  array<string, mixed>  $resultado
     * @param  array<string, mixed>  $filtros
     */
    private function exportExcel(array $resultado, array $filtros): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ranking');

        $sheet->setCellValue('A1', 'Ranking de mejores promedios');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF003200');

        $sheet->setCellValue('A2', 'Periodo: '.$filtros['periodo_label']);
        $sheet->setCellValue('D2', 'Materia: '.($filtros['materia']?->nombre ?? 'Promedio general'));
        $sheet->setCellValue('A3', 'Ámbito: '.$resultado['scope_label']);
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('D2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A2:G3')->getFont()->setSize(10)->getColor()->setARGB('FF33412F');

        $cabecera = ['Posición', 'Código', 'Estudiante', 'Curso', 'Materia', 'Promedio', 'Valoración'];
        foreach ($cabecera as $index => $texto) {
            $sheet->setCellValue($this->colLetter($index + 1).'5', $texto);
        }

        $sheet->getStyle('A5:G5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A5:G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF003200');
        $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $fila = 6;
        foreach ($resultado['filas'] as $item) {
            $section = $item->section;
            $sheet->setCellValue('A'.$fila, $item->posicion);
            $sheet->setCellValue('B'.$fila, $item->student->codigo);
            $sheet->setCellValue('C'.$fila, trim($item->student->apellidos.' '.$item->student->nombres));
            $sheet->setCellValue('D'.$fila, $section->codigoCompleto().' — '.($section->grade?->nombre ?? ''));
            $sheet->setCellValue('E'.$fila, $filtros['materia']?->nombre ?? 'General');
            $sheet->setCellValue('F'.$fila, $item->promedio);
            $sheet->setCellValue('G'.$fila, $item->valoracion['codigo'].' — '.$item->valoracion['nombre']);
            $fila++;
        }

        $ultimaFila = max(5, $fila - 1);
        $sheet->getStyle('A5:G'.$ultimaFila)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD1DFD1');
        foreach (range(1, 7) as $col) {
            $sheet->getColumnDimension($this->colLetter($col))->setAutoSize(true);
        }
        $sheet->freezePane('A6');
        $sheet->getStyle('A6:A'.$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F6:G'.$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $this->streamXlsx(
            $spreadsheet,
            'ranking-promedios_'.now()->format('Ymd').'.xlsx'
        );
    }

    private function colLetter(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }

    private function streamXlsx(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $contenido = ob_get_clean();

        return response()->streamDownload(
            fn () => print ($contenido),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
