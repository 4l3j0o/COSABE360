<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['estudiante', 'gestion'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('estudiante', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        $enrollments = $query->paginate(15)->withQueryString();
        $gestiones = AcademicYear::orderByDesc('fecha_inicio')->get();
        $estudiantes = Student::where('estado', '!=', 'ELIMINADO')->orderBy('apellidos')->get(['id', 'codigo', 'nombres', 'apellidos']);

        return view('enrollments.index', compact('enrollments', 'gestiones', 'estudiantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', Rule::in(Enrollment::ESTADOS)],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $existe = Enrollment::where('student_id', $validated['student_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('estado', '!=', 'ANULADA')
            ->exists();

        if ($existe) {
            return back()->withErrors([
                'student_id' => 'El estudiante ya tiene una inscripción activa en este período.',
            ])->withInput();
        }

        Enrollment::create($validated);

        return redirect()->route('enrollments.index')->with('mensaje', 'Inscripción registrada correctamente.');
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', Rule::in(Enrollment::ESTADOS)],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $existe = Enrollment::where('student_id', $validated['student_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('estado', '!=', 'ANULADA')
            ->where('id', '!=', $enrollment->id)
            ->exists();

        if ($existe) {
            return back()->withErrors([
                'student_id' => 'El estudiante ya tiene una inscripción activa en este período.',
            ])->withInput();
        }

        $enrollment->update($validated);

        return redirect()->route('enrollments.index')->with('mensaje', 'Inscripción actualizada correctamente.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->update(['estado' => 'ANULADA']);

        return redirect()->route('enrollments.index')->with('mensaje', 'Inscripción anulada correctamente.');
    }
}
