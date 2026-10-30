<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EnfermeriaAtencion;
use App\Models\EnfermeriaMotivo;
use App\Models\MedicalRecord;
use App\Models\Student;
use App\Services\EnfermeriaScopeService;
use App\Support\AccessScope;
use App\Support\SecurityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EnfermeriaController extends Controller
{
    public function __construct(private EnfermeriaScopeService $scope) {}

    public function index(Request $request)
    {
        $this->autorizar($request, ['enfermeria.ver', 'enfermeria.ver.nivel']);

        $atenciones = $this->consulta($request)
            ->with(['student', 'section.grade.level', 'motivo', 'nurse'])
            ->orderByDesc('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        $motivos = EnfermeriaMotivo::activos()->get();
        $hoy = now()->toDateString();
        $stats = [
            'hoy' => $this->consulta($request)->whereDate('fecha_hora', $hoy)->where('estado', '!=', 'ANULADO')->count(),
            'observacion' => $this->consulta($request)->where('estado', 'EN_OBSERVACION')->count(),
            'mes' => $this->consulta($request)->whereMonth('fecha_hora', now()->month)->whereYear('fecha_hora', now()->year)->where('estado', '!=', 'ANULADO')->count(),
        ];

        return view('enfermeria.index', compact('atenciones', 'motivos', 'stats'));
    }

    public function create(Request $request)
    {
        $this->autorizar($request, ['enfermeria.registrar']);

        $motivos = EnfermeriaMotivo::activos()->get();

        return view('enfermeria.create', compact('motivos'));
    }

    public function store(Request $request)
    {
        $this->autorizar($request, ['enfermeria.registrar']);

        $datos = $this->validar($request);
        $estudiante = Student::where('estado', 'ACTIVO')->findOrFail($datos['student_id']);
        $matricula = $estudiante->activeRegistration();

        $atencion = EnfermeriaAtencion::create([
            'student_id' => $estudiante->id,
            'section_id' => $matricula?->section_id,
            'academic_year_id' => $matricula?->enrollment?->academic_year_id,
            'nurse_user_id' => $request->user()->id,
            'motivo_id' => $datos['motivo_id'],
            'fecha_hora' => $datos['fecha_hora'],
            'atencion_indicacion' => $datos['atencion_indicacion'],
            'observaciones_internas' => $datos['observaciones_internas'] ?? null,
            'estado' => $datos['estado'],
            'tipo_derivacion' => $datos['estado'] === 'DERIVADO' ? $datos['tipo_derivacion'] : null,
        ]);

        SecurityLog::event('enfermeria.attention.created', [
            'atencion_id' => $atencion->id,
            'student_id' => $estudiante->id,
        ]);

        return redirect()->route('enfermeria.show', $atencion)
            ->with('success', 'Atención registrada correctamente.');
    }

    public function show(Request $request, EnfermeriaAtencion $atencion)
    {
        $this->autorizarAtencion($request, $atencion);

        $atencion->load([
            'student.parentGuardians',
            'student.medicalRecord',
            'section.grade.level',
            'motivo',
            'nurse',
        ]);

        return view('enfermeria.show', compact('atencion'));
    }

    public function edit(Request $request, EnfermeriaAtencion $atencion)
    {
        $this->autorizar($request, ['enfermeria.editar']);
        $this->autorizarAtencion($request, $atencion);

        $motivos = EnfermeriaMotivo::activos()->get();

        return view('enfermeria.edit', compact('atencion', 'motivos'));
    }

    public function update(Request $request, EnfermeriaAtencion $atencion)
    {
        $this->autorizar($request, ['enfermeria.editar']);
        $this->autorizarAtencion($request, $atencion);

        $datos = $this->validar($request);

        $atencion->update([
            'motivo_id' => $datos['motivo_id'],
            'fecha_hora' => $datos['fecha_hora'],
            'atencion_indicacion' => $datos['atencion_indicacion'],
            'observaciones_internas' => $datos['observaciones_internas'] ?? null,
            'estado' => $datos['estado'],
            'tipo_derivacion' => $datos['estado'] === 'DERIVADO' ? $datos['tipo_derivacion'] : null,
        ]);

        SecurityLog::event('enfermeria.attention.updated', ['atencion_id' => $atencion->id]);

        return redirect()->route('enfermeria.show', $atencion)
            ->with('success', 'Atención actualizada correctamente.');
    }

    public function anular(Request $request, EnfermeriaAtencion $atencion)
    {
        $this->autorizar($request, ['enfermeria.anular']);
        $this->autorizarAtencion($request, $atencion);

        $atencion->update(['estado' => 'ANULADO', 'tipo_derivacion' => null]);
        SecurityLog::event('enfermeria.attention.cancelled', ['atencion_id' => $atencion->id]);

        return back()->with('success', 'Atención anulada correctamente.');
    }

    public function buscarEstudiante(Request $request)
    {
        $this->autorizar($request, ['enfermeria.ver', 'enfermeria.ver.nivel']);

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $buscable = addcslashes($q, '%_');
        $query = Student::query()
            ->where('estado', 'ACTIVO')
            ->where(function ($sub) use ($buscable) {
                $sub->where('nombres', 'like', "%{$buscable}%")
                    ->orWhere('apellidos', 'like', "%{$buscable}%")
                    ->orWhere('codigo', 'like', "%{$buscable}%")
                    ->orWhere('numero_documento', 'like', "%{$buscable}%");
            });

        if (! $request->user()->hasAnyRole(['Enfermera', 'Administrador'])) {
            $query = AccessScope::aplicarEstudiantes($query, $request->user());
        }

        return response()->json(
            $query->with('registrations.section.grade.level')->orderBy('apellidos')->orderBy('nombres')->limit(20)->get()
                ->map(fn (Student $student) => [
                    'id' => $student->id,
                    'nombre' => trim($student->apellidos.' '.$student->nombres),
                    'codigo' => $student->codigo,
                    'curso' => $student->activeSection()?->codigoCorto() ?? 'Sin curso',
                ])
        );
    }

    public function updateFicha(Request $request, Student $student)
    {
        $this->autorizar($request, ['salud.editar']);

        $user = $request->user();
        abort_unless(
            $user->hasAnyRole(['Enfermera', 'Administrador']) || AccessScope::puedeVerEstudiante($user, $student),
            403
        );

        $datos = $request->validate([
            'grupo_sanguineo' => ['nullable', 'string', 'max:5'],
            'alergias' => ['nullable', 'string', 'max:2000'],
            'medicamentos' => ['nullable', 'string', 'max:2000'],
            'condiciones_especiales' => ['nullable', 'string', 'max:2000'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:150'],
            'contacto_emergencia_telefono' => ['nullable', 'string', 'max:20'],
            'contacto_emergencia_parentesco' => ['nullable', 'string', 'max:50'],
        ]);

        MedicalRecord::updateOrCreate(['student_id' => $student->id], $datos);
        SecurityLog::event('medical_record.updated', ['student_id' => $student->id]);

        return back()->with('success', 'Ficha médica actualizada correctamente.');
    }

    public function miEnfermeria(Request $request)
    {
        $this->autorizar($request, ['enfermeria.ver.propio']);

        $student = $request->user()->student;
        abort_unless($student, 403, 'Sin perfil de estudiante.');

        $atenciones = EnfermeriaAtencion::with('motivo')
            ->where('student_id', $student->id)
            ->where('estado', '!=', 'ANULADO')
            ->orderByDesc('fecha_hora')
            ->paginate(15);

        $total = EnfermeriaAtencion::where('student_id', $student->id)
            ->where('estado', '!=', 'ANULADO')
            ->count();

        return view('enfermeria.mi-enfermeria', compact('student', 'atenciones', 'total'));
    }

    public function reportes(Request $request)
    {
        $this->autorizar($request, ['enfermeria.reportes', 'enfermeria.ver.nivel']);

        $datos = $this->datosReporte($request);

        return view('enfermeria.reportes', $datos);
    }

    public function exportar(Request $request)
    {
        $this->autorizar($request, ['enfermeria.reportes.exportar', 'enfermeria.ver.nivel']);

        $datos = $this->datosReporte($request);
        $formato = $request->query('format') === 'excel' ? 'excel' : 'pdf';

        if ($formato === 'excel') {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Enfermería');
            $sheet->fromArray(['Fecha', 'Estudiante', 'Curso', 'Motivo', 'Estado', 'Derivación'], null, 'A1');
            $fila = 2;
            foreach ($datos['atenciones'] as $a) {
                $sheet->fromArray([
                    $a->fecha_hora?->format('d/m/Y H:i'),
                    trim(($a->student?->apellidos ?? '').' '.($a->student?->nombres ?? '')),
                    $a->section?->codigoCorto() ?? '',
                    $a->motivo?->nombre ?? '',
                    $a->estado,
                    $a->tipo_derivacion ?? '',
                ], null, 'A'.$fila++);
            }

            return response()->streamDownload(
                fn () => (new Xlsx($spreadsheet))->save('php://output'),
                'reporte-enfermeria-'.now()->format('Ymd').'.xlsx'
            );
        }

        $pdf = Pdf::loadView('enfermeria.reportes-pdf', $datos)->setPaper('a4', 'landscape');

        return $pdf->download('reporte-enfermeria-'.now()->format('Ymd').'.pdf');
    }

    private function consulta(Request $request)
    {
        $query = $this->scope->queryAtenciones($request->user());

        if ($buscar = trim((string) $request->query('buscar', ''))) {
            $buscable = addcslashes($buscar, '%_');
            $query->whereHas('student', function ($q) use ($buscable) {
                $q->where('nombres', 'like', "%{$buscable}%")
                    ->orWhere('apellidos', 'like', "%{$buscable}%")
                    ->orWhere('codigo', 'like', "%{$buscable}%")
                    ->orWhere('numero_documento', 'like', "%{$buscable}%");
            });
        }

        if ($request->filled('motivo_id')) {
            $query->where('motivo_id', (int) $request->query('motivo_id'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_hora', '>=', $request->query('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->query('hasta'));
        }

        return $query;
    }

    private function datosReporte(Request $request): array
    {
        $atenciones = $this->consulta($request)
            ->with(['student', 'section.grade.level', 'motivo'])
            ->orderByDesc('fecha_hora')
            ->get();

        return [
            'atenciones' => $atenciones,
            'motivos' => EnfermeriaMotivo::orderBy('nombre')->get(),
            'porEstudiante' => $atenciones->groupBy('student_id')->map(fn ($items) => [
                'estudiante' => trim(($items->first()->student?->apellidos ?? '').' '.($items->first()->student?->nombres ?? '')),
                'total' => $items->count(),
            ])->sortByDesc('total')->take(20)->values(),
            'porMotivo' => $atenciones->groupBy(fn ($a) => $a->motivo?->nombre ?? 'Sin motivo')->map->count()->sortDesc(),
            'porCurso' => $atenciones->groupBy(fn ($a) => $a->section?->codigoCorto() ?? 'Sin curso')->map->count()->sortDesc(),
            'porMes' => $atenciones->groupBy(fn ($a) => $a->fecha_hora?->format('Y-m'))->map->count()->sortKeysDesc(),
            'filtros' => $request->only(['buscar', 'motivo_id', 'estado', 'desde', 'hasta']),
        ];
    }

    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'motivo_id' => ['required', 'integer', 'exists:enfermeria_motivos,id'],
            'fecha_hora' => ['required', 'date'],
            'atencion_indicacion' => ['required', 'string', 'max:4000'],
            'observaciones_internas' => ['nullable', 'string', 'max:4000'],
            'estado' => ['required', Rule::in(EnfermeriaAtencion::ESTADOS)],
            'tipo_derivacion' => ['nullable', Rule::in(EnfermeriaAtencion::DERIVACIONES), 'required_if:estado,DERIVADO'],
        ]);

        if ($datos['estado'] !== 'DERIVADO') {
            $datos['tipo_derivacion'] = null;
        }

        return $datos;
    }

    private function autorizar(Request $request, array $permisos): void
    {
        abort_unless($request->user()?->puede($permisos), 403);
    }

    private function autorizarAtencion(Request $request, EnfermeriaAtencion $atencion): void
    {
        abort_unless($this->scope->puedeVerAtencion($request->user(), $atencion), 403);
    }
}
