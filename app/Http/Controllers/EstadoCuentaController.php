<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Módulo de Pensiones: estado de cuentas por grado/paralelo e historial
 * de pagos por rango de fechas.
 */
class EstadoCuentaController extends Controller
{
    private const ROLES = ['Administrador', 'Secretaría', 'Director'];

    private const MESES = [
        2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo',
        6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
        10 => 'Octubre', 11 => 'Noviembre',
    ];

    /**
     * Estado de cuentas de los estudiantes del grado/paralelo elegido.
     */
    public function estadoCuentas(Request $request): View
    {
        $this->autorizar($request);

        $gestiones = AcademicYear::orderByDesc('nombre')->get();
        $periodo = $this->resolverPeriodo($request, $gestiones);

        $gradeId = $request->integer('grade_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $estadoFiltro = $request->query('estado') ?: null;

        $grades = $periodo
            ? Grade::with('level')
                ->whereHas('sections', fn ($q) => $q->where('academic_year_id', $periodo->id))
                ->get()
                ->sortBy([['level.orden', 'asc'], ['orden', 'asc']])
                ->values()
            : collect();

        $secciones = $periodo
            ? Section::with(['grade', 'gestion'])
                ->where('academic_year_id', $periodo->id)
                ->when($gradeId, fn ($q) => $q->where('grade_id', $gradeId))
                ->ordenadoPorNivel()
                ->get()
            : collect();

        $matriculas = $periodo
            ? Registration::query()
                ->with(['enrollment.estudiante', 'section.grade', 'section.gestion'])
                ->where('registrations.estado', 'ACTIVA')
                ->whereHas('enrollment', fn ($q) => $q
                    ->where('estado', 'COMPLETADA')
                    ->where('academic_year_id', $periodo->id))
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->when(! $sectionId && $gradeId, fn ($q) => $q->whereHas('section', fn ($s) => $s->where('grade_id', $gradeId)))
                ->get()
            : collect();

        $cuotasPorMatricula = MonthlyPayment::query()
            ->whereIn('registration_id', $matriculas->pluck('id'))
            ->get(['registration_id', 'mes', 'anio', 'monto', 'monto_esperado', 'monto_pagado', 'estado'])
            ->groupBy('registration_id');

        $filas = $matriculas
            ->map(fn (Registration $reg) => $this->filaEstadoCuenta($reg, $cuotasPorMatricula->get($reg->id, collect())))
            ->when($estadoFiltro, fn (Collection $c) => $c->where('estado', $estadoFiltro))
            ->sortBy(fn ($f) => [$f['curso'], $f['estudiante']])
            ->values();

        $resumen = [
            'estudiantes' => $filas->count(),
            'esperado' => round($filas->sum('esperado'), 2),
            'pagado' => round($filas->sum('pagado'), 2),
            'saldo' => round($filas->sum('saldo'), 2),
            'al_dia' => $filas->where('estado', 'AL DÍA')->count(),
            'con_deuda' => $filas->whereIn('estado', ['PENDIENTE', 'VENCIDO'])->count(),
        ];

        $page = Paginator::resolveCurrentPage();
        $porPagina = 25;
        $filasPaginadas = new LengthAwarePaginator(
            $filas->forPage($page, $porPagina)->values(),
            $filas->count(),
            $porPagina,
            $page,
            ['path' => route('pensiones.estado-cuentas'), 'query' => $request->query()]
        );

        return view('pensiones.estado-cuentas', [
            'gestiones' => $gestiones,
            'periodo' => $periodo,
            'grades' => $grades,
            'secciones' => $secciones,
            'filas' => $filasPaginadas,
            'resumen' => $resumen,
            'filtros' => [
                'academic_year_id' => $periodo?->id,
                'grade_id' => $gradeId,
                'section_id' => $sectionId,
                'estado' => $estadoFiltro,
            ],
        ]);
    }

    /**
     * Historial de pagos registrados entre dos fechas.
     */
    public function historial(Request $request): View
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ], [
            'hasta.after_or_equal' => 'La fecha "hasta" no puede ser anterior a "desde".',
        ]);

        $desde = isset($validated['desde'])
            ? Carbon::parse($validated['desde'])->startOfDay()
            : now()->startOfYear();
        $hasta = isset($validated['hasta'])
            ? Carbon::parse($validated['hasta'])->endOfDay()
            : now()->endOfDay();

        $periodo = AcademicYear::where('estado', 'ACTIVA')->first();

        $secciones = Section::with(['grade', 'gestion'])
            ->when($periodo, fn ($q) => $q->where('academic_year_id', $periodo->id))
            ->ordenadoPorNivel()
            ->get();

        $sectionId = $request->integer('section_id') ?: null;
        $estadoFiltro = $request->query('estado') ?: null;
        $buscar = trim((string) $request->query('buscar'));

        $pagos = MonthlyPayment::query()
            ->with([
                'registration.enrollment.estudiante',
                'registration.section.grade',
                'registration.section.gestion',
                'electronicInvoice',
            ])
            ->whereNotNull('fecha')
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($sectionId, fn ($q) => $q->whereHas('registration', fn ($r) => $r->where('section_id', $sectionId)))
            ->when($estadoFiltro, fn ($q) => $q->where('estado', $estadoFiltro))
            ->when($buscar !== '', fn ($q) => $q->whereHas('registration.enrollment.estudiante', function ($e) use ($buscar) {
                $e->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
            }))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $resumen = [
            'cantidad' => $pagos->total(),
            'cobrado' => round((float) MonthlyPayment::query()
                ->whereNotNull('fecha')
                ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
                ->whereIn('estado', ['PAGADA', 'PAGADO'])
                ->when($sectionId, fn ($q) => $q->whereHas('registration', fn ($r) => $r->where('section_id', $sectionId)))
                ->when($estadoFiltro, fn ($q) => $q->where('estado', $estadoFiltro))
                ->sum('monto'), 2),
        ];

        return view('pensiones.historial-pagos', [
            'pagos' => $pagos,
            'secciones' => $secciones,
            'resumen' => $resumen,
            'filtros' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
                'section_id' => $sectionId,
                'estado' => $estadoFiltro,
                'buscar' => $buscar,
            ],
            'meses' => self::MESES,
        ]);
    }

    /**
     * Agrega las cuotas de una matrícula en una fila de estado de cuenta.
     */
    private function filaEstadoCuenta(Registration $reg, Collection $cuotas): array
    {
        $esperado = 0.0;
        $pagado = 0.0;
        $vencidas = 0;
        $pendientes = 0;

        foreach ($cuotas as $cuota) {
            $montoEsperado = (float) ($cuota->monto_esperado > 0 ? $cuota->monto_esperado : $cuota->monto);
            $esperado += $montoEsperado;

            if (in_array($cuota->estado, ['PAGADA', 'PAGADO'], true)) {
                $pagado += (float) $cuota->monto;
            } elseif ($cuota->estado === 'PAGO_PARCIAL') {
                $pagado += (float) $cuota->monto_pagado;
                $pendientes++;
            } elseif (in_array($cuota->estado, ['PENDIENTE', 'ESPERA'], true)) {
                $pendientes++;
            } elseif ($cuota->estado === 'VENCIDO') {
                $vencidas++;
            }
        }

        $esperado = round($esperado, 2);
        $pagado = round(min($pagado, $esperado), 2);
        $saldo = round(max($esperado - $pagado, 0), 2);

        $estado = match (true) {
            $cuotas->isEmpty() => 'SIN CUOTAS',
            $saldo <= 0.009 => 'AL DÍA',
            $vencidas > 0 => 'VENCIDO',
            default => 'PENDIENTE',
        };

        $estudiante = $reg->enrollment?->estudiante;

        return [
            'estudiante' => $estudiante ? trim($estudiante->apellidos.' '.$estudiante->nombres) : '—',
            'estudiante_id' => $estudiante?->id,
            'codigo' => $estudiante?->codigo ?? '—',
            'curso' => $reg->section?->codigoCorto() ?? '—',
            'esperado' => $esperado,
            'pagado' => $pagado,
            'saldo' => $saldo,
            'cuotas' => $cuotas->count(),
            'pagadas' => $cuotas->whereIn('estado', ['PAGADA', 'PAGADO'])->count(),
            'pendientes' => $pendientes,
            'vencidas' => $vencidas,
            'estado' => $estado,
        ];
    }

    private function resolverPeriodo(Request $request, Collection $gestiones): ?AcademicYear
    {
        $id = $request->integer('academic_year_id');

        if ($id) {
            return $gestiones->firstWhere('id', $id);
        }

        return $gestiones->firstWhere('estado', 'ACTIVA') ?? $gestiones->first();
    }

    private function autorizar(Request $request): void
    {
        abort_unless($request->user() && $request->user()->hasAnyRole(self::ROLES), 403);
    }
}
