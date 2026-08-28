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
     * Listado de pagos (mensualidades) registrados.
     */
    public function index(Request $request)
    {
        $query = MonthlyPayment::with(['enrollment.estudiante'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('enrollment.estudiante', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        $pagos = $query->paginate(15)->withQueryString();

        return view('monthly-payments.index', compact('pagos'));
    }

    /**
     * Formulario para registrar un pago de mensualidad.
     */
    public function create()
    {
        // Estudiantes con inscripción completada (tienen matrícula activa potencial)
        $estudiantes = Student::where('estado', 'ACTIVO')
            ->whereHas('enrollments', function ($q) {
                $q->where('estado', 'COMPLETADA')
                    ->whereHas('registration', function ($r) {
                        $r->where('estado', 'ACTIVA');
                    });
            })
            ->orderBy('apellidos')
            ->get(['id', 'codigo', 'nombres', 'apellidos']);

        return view('monthly-payments.create', compact('estudiantes'));
    }

    /**
     * CA-19-01: Registrar un pago con monto, fecha y método de pago.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => ['required', 'exists:students,id'],
            'mes'           => ['required', 'integer', 'between:1,12'],
            'monto'         => ['required', 'numeric', 'min:0.01'],
            'fecha'         => ['required', 'date'],
            'metodo_pago'   => ['required', Rule::in(MonthlyPayment::METODOS_PAGO)],
        ]);

        $estudiante = Student::findOrFail($validated['estudiante_id']);

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
                'estudiante_id' => 'El estudiante no tiene una matrícula activa para registrar el pago.',
            ])->withInput();
        }

        // Crear (o actualizar) la cuota mensual correspondiente y marcarla como pagada
        $cuota = MonthlyPayment::updateOrCreate(
            [
                'enrollment_id' => $inscripcion->id,
                'mes'           => $validated['mes'],
            ],
            [
                'monto'        => $validated['monto'],
                'fecha'        => $validated['fecha'],
                'metodo_pago'  => $validated['metodo_pago'],
                'estado'       => 'PAGADA',
            ]
        );

        return redirect()->route('monthly-payments.index')
            ->with('mensaje', 'Pago de la mensualidad registrado correctamente.');
    }
}
