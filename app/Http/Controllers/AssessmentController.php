<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $assessments = Assessment::with(['subject', 'academicYear'])
            ->when($request->query('materia_id'), fn ($q, $v) => $q->where('materia_id', $v))
            ->when($request->query('gestion_id'), fn ($q, $v) => $q->where('gestion_id', $v))
            ->orderByDesc('fecha')
            ->get();

        $subjects = Subject::orderBy('nombre')->get();
        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('assessments.index', compact('assessments', 'subjects', 'academicYears'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('nombre')->get();
        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('assessments.create', compact('subjects', 'academicYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'max:50'],
            'materia_id' => ['required', 'integer', 'exists:subjects,id'],
            'gestion_id' => ['required', 'integer', 'exists:academic_years,id'],
            'peso' => ['required', 'numeric', 'min:0', 'max:1'],
            'fecha' => ['nullable', 'date'],
        ]);

        Assessment::create($validated);

        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación registrada correctamente.');
    }

    public function edit(Assessment $assessment): View
    {
        $subjects = Subject::orderBy('nombre')->get();
        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('assessments.edit', compact('assessment', 'subjects', 'academicYears'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'max:50'],
            'materia_id' => ['required', 'integer', 'exists:subjects,id'],
            'gestion_id' => ['required', 'integer', 'exists:academic_years,id'],
            'peso' => ['required', 'numeric', 'min:0', 'max:1'],
            'fecha' => ['nullable', 'date'],
        ]);

        $assessment->update($validated);

        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $assessment->delete();

        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación eliminada correctamente.');
    }
}
