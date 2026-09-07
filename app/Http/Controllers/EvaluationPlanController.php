<?php

namespace App\Http\Controllers;

use App\Models\CourseSubject;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationPlan;
use App\Models\Student;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationPlanController extends Controller
{
    /**
     * Evaluaciones (planes) por trimestre de un course_subject (curso-materia-docente).
     */
    public function index(Request $request, CourseSubject $courseSubject): View
    {
        $this->autorizar($request, $courseSubject);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion']);

        $plans = $courseSubject->evaluationPlans()
            ->withCount('criteria')
            ->orderBy('trimestre')
            ->get();

        return view('evaluations.index', compact('courseSubject', 'plans'));
    }

    /**
     * Crea (si no existe) el plan de un trimestre y sus 4 criterios por defecto.
     */
    public function guardar(Request $request, CourseSubject $courseSubject): RedirectResponse
    {
        $this->autorizar($request, $courseSubject);

        $validated = $request->validate([
            'trimestre' => ['required', 'integer', 'in:1,2,3'],
            'nombre' => ['nullable', 'string', 'max:150'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $service = app(EvaluationService::class);
        $service->obtenerOCrearPlan(
            $courseSubject,
            $validated['trimestre'],
            $validated['nombre'] ?? null
        );

        $plan = EvaluationPlan::where('course_subject_id', $courseSubject->id)
            ->where('trimestre', $validated['trimestre'])
            ->first();

        if (! empty($validated['fecha_inicio'])) {
            $plan->update([
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'] ?? null,
            ]);
        }

        return redirect()->route('evaluations.show', [$courseSubject, $plan])
            ->with('success', 'Evaluación del trimestre '.$validated['trimestre'].' creada.');
    }

    /**
     * Detalle del plan: criterios, actividades y notas por estudiante.
     */
    public function show(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): View
    {
        $this->autorizar($request, $courseSubject);
        abort_unless($plan->course_subject_id === $courseSubject->id, 404);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion']);

        $plan->load(['criteria.activities.grades', 'criteria.activities.grades.student']);
        $service = app(EvaluationService::class);

        // Estudiantes del curso (matriculados y activos)
        $students = $courseSubject->course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->get();

        $estado = $plan->estado;

        return view('evaluations.show', compact('courseSubject', 'plan', 'students', 'service', 'estado'));
    }

    /**
     * Agrega una actividad a un criterio (SABER/HACER).
     */
    public function agregarActividad(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $courseSubject);

        $validated = $request->validate([
            'evaluation_criteria_id' => ['required', 'integer', 'exists:evaluation_criteria,id'],
            'nombre' => ['required', 'string', 'max:200'],
            'fecha' => ['nullable', 'date'],
        ]);

        $criterion = EvaluationCriterion::where('id', $validated['evaluation_criteria_id'])
            ->where('evaluation_plan_id', $plan->id)
            ->firstOrFail();

        $orden = $criterion->activities()->max('orden') + 1;

        app(EvaluationService::class)->crearActividad(
            $criterion,
            $validated['nombre'],
            $validated['fecha'] ?? null,
            $orden ?: 1
        );

        return redirect()->route('evaluations.show', [$courseSubject, $plan])
            ->with('success', 'Actividad agregada al criterio '.$criterion->codigo.'.');
    }

    /**
     * Registra/actualiza las notas de todos los estudiantes en una actividad.
     */
    public function guardarNotas(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $courseSubject);

        $validated = $request->validate([
            'evaluation_activity_id' => ['required', 'integer', 'exists:evaluation_activities,id'],
            'notas' => ['required', 'array'],
            'notas.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $activity = $plan->criteria()
            ->with('activities')
            ->get()
            ->flatMap->activities
            ->firstWhere('id', $validated['evaluation_activity_id']);

        abort_unless($activity, 404);

        $service = app(EvaluationService::class);

        foreach ($validated['notas'] as $studentId => $nota) {
            if ($nota === null || $nota === '') {
                continue;
            }

            $student = Student::find($studentId);
            if ($student) {
                $service->registrarNota($activity, $student, (float) $nota, null, $request->user()->id);
            }
        }

        return redirect()->route('evaluations.show', [$courseSubject, $plan])
            ->with('success', 'Notas registradas en la actividad.');
    }

    private function autorizar(Request $request, CourseSubject $courseSubject): void
    {
        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $teacher->id !== $courseSubject->teacher_id, 403);
    }
}
