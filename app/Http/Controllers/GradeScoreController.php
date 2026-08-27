<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\GradeScore;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeScoreController extends Controller
{
    public function index(): View
    {
        $assessments = Assessment::with(['subject', 'academicYear'])
            ->withCount('gradeScores')
            ->orderByDesc('fecha')
            ->get();

        return view('grade-scores.index', compact('assessments'));
    }

    public function show(Assessment $assessment): View
    {
        $assessment->load(['subject', 'academicYear']);

        $students = Student::where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $notasExistentes = GradeScore::where('evaluacion_id', $assessment->id)
            ->get()
            ->keyBy('estudiante_id');

        return view('grade-scores.show', compact('assessment', 'students', 'notasExistentes'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $request->validate([
            'notas' => ['array'],
            'notas.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'observaciones' => ['array'],
        ]);

        $notas = $request->input('notas', []);
        $observaciones = $request->input('observaciones', []);

        foreach ($notas as $estudianteId => $nota) {
            if ($nota === null || $nota === '') {
                continue;
            }

            GradeScore::updateOrCreate(
                ['evaluacion_id' => $assessment->id, 'estudiante_id' => $estudianteId],
                [
                    'nota' => $nota,
                    'observaciones' => $observaciones[$estudianteId] ?? null,
                ]
            );
        }

        return redirect()->route('calificaciones.show', $assessment)
            ->with('success', 'Calificaciones registradas correctamente.');
    }
}
