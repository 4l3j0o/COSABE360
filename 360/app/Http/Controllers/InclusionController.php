<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentCurricularAdaptation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-54: Educación inclusiva (Ley 070 Arts. 25-27).
 * Registra adaptaciones curriculares y evaluación diferenciada por estudiante.
 */
class InclusionController extends Controller
{
    public function show(Request $request, Student $student): View
    {
        $this->autorizar($request);

        $student->load(['curricularAdaptations', 'rudeRecord']);

        return view('inclusion.show', compact('student'));
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'condicion' => ['required', 'in:'.implode(',', array_keys(StudentCurricularAdaptation::CONDICIONES))],
            'tipo_discapacidad' => ['nullable', 'string', 'max:150'],
            'grado' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'plan_adaptacion' => ['nullable', 'string', 'max:2000'],
            'modalidad' => ['nullable', 'string', 'max:100'],
            'evaluacion_diferenciada' => ['nullable', 'boolean'],
        ]);

        $student->curricularAdaptations()->updateOrCreate(
            ['condicion' => $validated['condicion']],
            [
                'tipo_discapacidad' => $validated['tipo_discapacidad'] ?? null,
                'grado' => $validated['grado'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'plan_adaptacion' => $validated['plan_adaptacion'] ?? null,
                'modalidad' => $validated['modalidad'] ?? null,
                'evaluacion_diferenciada' => (bool) ($validated['evaluacion_diferenciada'] ?? false),
                'estado' => 'ACTIVO',
            ]
        );

        return redirect()->route('inclusion.show', $student)
            ->with('success', 'Adaptación curricular registrada para '.$student->nombres.' '.$student->apellidos.'.');
    }

    public function destroy(Request $request, Student $student, StudentCurricularAdaptation $adaptation): RedirectResponse
    {
        $this->autorizar($request);

        abort_unless($adaptation->student_id === $student->id, 404);

        $adaptation->update(['estado' => 'INACTIVO']);

        return redirect()->route('inclusion.show', $student)
            ->with('success', 'Adaptación desactivada.');
    }

    private function autorizar(Request $request): void
    {
        abort_unless($request->user()->puede('expediente.gestionar'), 403);
    }
}
