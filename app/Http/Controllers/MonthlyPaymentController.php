<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonthlyPaymentController extends Controller
{
    /**
     * Listado general de pagos (acceso secundario).
     */
    public function index(Request $request)
    {
        $query = MonthlyPayment::with(['registration.enrollment.estudiante'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('registration.enrollment.estudiante', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        $pagos = $query->paginate(15)->withQueryString();

        return view('monthly-payments.index', compact('pagos'));
    }

    /**
     * CA-19-02/03: Pagos y saldo de un único estudiante.
     */
    public function estudiante(Student $estudiante)
    {
        $inscripcion = $estudiante->enrollments()
            ->with('gestion')
            ->where('estado', 'COMPLETADA')
            ->whereHas('registration', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->latest('fecha')
            ->first();

        $pagos = collect();
        $saldoPendiente = 0.0;
        $totalPagado = 0.0;
        $mesesPendientes = 0;
        $mesesPagados = 0;

        $matricula = $inscripcion?->registration;
        if ($matricula) {
            $pagos = $matricula->monthlyPayments()
                ->orderBy('mes')
                ->get();

            $saldoPendiente = (float) $pagos->where('estado', 'PENDIENTE')->sum('monto');
            $totalPagado = (float) $pagos->where('estado', 'PAGADA')->sum('monto');
            $mesesPendientes = $pagos->where('estado', 'PENDIENTE')->count();
            $mesesPagados = $pagos->where('estado', 'PAGADA')->count();
        }

        return view('monthly-payments.estudiante', compact(
            'estudiante',
            'inscripcion',
            'pagos',
            'saldoPendiente',
            'totalPagado',
            'mesesPendientes',
            'mesesPagados'
        ));
    }

    /**
     * CA-19-01: Registrar un pago con fecha y método de pago para un estudiante.
     * El monto se toma de la cuota ya generada en la matrícula.
     */
    public function storeEstudiante(Request $request, Student $estudiante)
    {
        $validated = $request->validate([
            'mes'           => ['required', 'integer', 'between:2,11'],
            'fecha'         => ['required', 'date'],
            'metodo_pago'   => ['required', Rule::in(MonthlyPayment::METODOS_PAGO)],
        ]);

        // Recuperar la inscripción completada con matrícula activa del estudiante
        $inscripcion = $estudiante->enrollments()
            ->where('estado', 'COMPLETADA')
            ->whereHas('registration', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->latest('fecha')
            ->first();

        if (! $inscripcion) {
            return back()->withErrors([
                'mes' => 'El estudiante no tiene una matrícula activa para registrar el pago.',
            ])->withInput();
        }

        $cuota = $inscripcion->registration->monthlyPayments()
            ->where('mes', $validated['mes'])
            ->first();

        if (! $cuota) {
            return back()->withErrors([
                'mes' => 'No se encontró la mensualidad del mes seleccionado para este estudiante.',
            ])->withInput();
        }

        if ($cuota->estado === 'PAGADA') {
            return back()->withErrors([
                'mes' => 'La mensualidad de este mes ya se encuentra pagada.',
            ])->withInput();
        }

        $cuota->update([
            'fecha'        => $validated['fecha'],
            'metodo_pago'  => $validated['metodo_pago'],
            'estado'       => 'PAGADA',
        ]);

        return redirect()->route('monthly-payments.estudiante', $estudiante)
            ->with('mensaje', 'Pago de la mensualidad registrado correctamente.');
    }
}
