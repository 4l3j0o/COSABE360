<?php

namespace App\Http\Controllers;

use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use App\Models\EvaluationSecondAttempt;
use App\Models\Student;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-51: Segunda instancia / examen de recuperación de estudiantes reprobados (R.M. 473/2021).
 * Lista reprobados de un trimestre, habilita la recuperación y registra la nueva nota.
 */
class SegundaInstanciaController extends Controller
{
    public function index(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): View
    {
        $this->autorizar($request, $courseSubject);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion']);
        $plan->load('criteria.activities');

        $service = app(EvaluationService::class);
        $reprobados = $service->reprobadosParaSegundaInstancia($plan);

        $attempts = EvaluationSecondAttempt::where('evaluation_plan_id', $plan->id)
            ->get()
            ->keyBy('student_id');

        return view('segunda-instancia.index', compact('courseSubject', 'plan', 'reprobados', 'attempts', 'service'));
    }

    public function registrar(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $courseSubject);

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'nota' => ['required', 'numeric', 'min:0', 'max:100'],
            'fecha' => ['nullable', 'date'],
        ]);

        $service = app(EvaluationService::class);

        // Habilitar y registrar la recuperación
        $student = Student::findOrFail($validated['student_id']);
        $attempt = $service->habilitarSegundaInstancia($plan, $student, $request->user()->id);
        $service->registrarSegundaInstancia($attempt, (float) $validated['nota'], $validated['fecha'] ?? null, $request->user()->id);

        return redirect()->route('segunda-instancia.index', [$courseSubject, $plan])
            ->with('success', 'Segunda instancia registrada para '.$student->apellidos.' '.$student->nombres.'.');
    }

    private function autorizar(Request $request, CourseSubject $courseSubject): void
    {
        $user = $request->user();
        $teacher = $user->teacher;

        $esDocenteTitular = $teacher && $teacher->id === $courseSubject->teacher_id;

        abort_if(! ($user->hasAnyRole(['Administrador', 'Director', 'Secretaría']) || $esDocenteTitular), 403);
    }
}
