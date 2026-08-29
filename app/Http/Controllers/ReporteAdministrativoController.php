<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\StudentDocument;
use Illuminate\Http\Request;

class ReporteAdministrativoController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        if ($request->expectsJson()) {
            return response()->json(['data' => ['academic_years' => $academicYears]]);
        }
        return view('reportes.administrativos.index', compact('academicYears'));
    }

    public function inscripciones(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'estado' => ['nullable', 'in:PENDIENTE,COMPLETADA,ANULADA'],
        ]);

        $query = Enrollment::with(['student', 'academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha');

        $result = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['data' => $result]);
        }

        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        return view('reportes.administrativos.inscripciones', ['inscripciones' => $result, 'academicYears' => $academicYears, 'filtros' => $request->only(['academic_year_id', 'estado'])]);
    }

    public function matriculas(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'estado' => ['nullable', 'in:ACTIVA,INACTIVA,CANCELADA'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ]);

        $query = Registration::with(['enrollment.student', 'course.grade', 'course.section', 'enrollment.academicYear'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->where('course_id', $v))
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('id');

        $result = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['data' => $result]);
        }

        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        return view('reportes.administrativos.matriculas', ['matriculas' => $result, 'academicYears' => $academicYears, 'filtros' => $request->only(['academic_year_id', 'estado', 'course_id'])]);
    }

    public function pagos(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'estado' => ['nullable', 'in:PENDIENTE,PAGO_PARCIAL,PAGADO,VENCIDO'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ]);

        $query = MonthlyPayment::with(['registration.enrollment.student', 'registration.course'])
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->when($request->course_id, fn ($q, $v) => $q->whereHas('registration', fn ($rq) => $rq->where('course_id', $v)))
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('registration.enrollment', fn ($eq) => $eq->where('academic_year_id', $v)))
            ->orderBy('anio')->orderBy('mes');

        $result = $query->paginate(20)->withQueryString();
        $deudaTotal = (clone $query)->whereIn('estado', ['PENDIENTE', 'VENCIDO', 'PAGO_PARCIAL'])->sum('monto_esperado');

        if ($request->expectsJson()) {
            return response()->json(['data' => $result, 'deuda_total' => $deudaTotal]);
        }

        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        return view('reportes.administrativos.pagos', ['pagos' => $result, 'deudaTotal' => $deudaTotal, 'academicYears' => $academicYears, 'filtros' => $request->only(['academic_year_id', 'estado', 'course_id'])]);
    }

    public function documentacion(Request $request)
    {
        $request->validate([
            'estado' => ['nullable', 'in:PENDIENTE,ENTREGADO,VERIFICADO'],
            'tipo_documento' => ['nullable', 'string', 'max:50'],
        ]);

        $query = StudentDocument::with(['student'])
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->when($request->tipo_documento, fn ($q, $v) => $q->where('tipo_documento', $v))
            ->orderByDesc('id');

        $result = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['data' => $result]);
        }

        return view('reportes.administrativos.documentacion', ['documentos' => $result, 'filtros' => $request->only(['estado', 'tipo_documento'])]);
    }
}
