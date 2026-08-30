<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', Rule::in(Enrollment::ESTADOS)],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $existente = Enrollment::where('student_id', $validated['student_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('estado', '!=', 'ANULADA')
            ->latest('fecha')
            ->first();

        if ($existente) {
            $existente->update([
                'fecha' => $validated['fecha'],
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?? $existente->observaciones,
            ]);

            return redirect()->route('students.index')->with('mensaje', 'Inscripción actualizada correctamente.');
        }

        Enrollment::create($validated);

        return redirect()->route('students.index')->with('mensaje', 'Inscripción registrada correctamente.');
    }
}
