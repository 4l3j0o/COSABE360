<?php

namespace App\Http\Controllers;

use App\Models\CourseSubject;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationPlan;
use App\Models\Student;
use App\Services\EvaluationService;
use App\Services\EvaluationTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Registra/actualiza las notas de todos los estudiantes en una o varias actividades
     * de un criterio (formato notas[activity_id][student_id] = valor).
     */
    public function guardarNotas(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $courseSubject);

        $validated = $request->validate([
            'notas' => ['required', 'array'],
            'notas.*' => ['array'],
            'notas.*.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $service = app(EvaluationService::class);
        $contador = 0;

        // Notas por actividad (formato: notas[activity_id][student_id])
        if (isset($validated['notas']) && is_array($validated['notas'])) {
            foreach ($validated['notas'] as $activityId => $notasEstudiante) {
                $activity = $plan->criteria()
                    ->with('activities')
                    ->get()
                    ->flatMap->activities
                    ->firstWhere('id', (int) $activityId);

                if (! $activity) {
                    continue;
                }

                foreach ($notasEstudiante as $studentId => $nota) {
                    if ($nota === null || $nota === '') {
                        continue;
                    }
                    $student = Student::find($studentId);
                    if ($student) {
                        $service->registrarNota($activity, $student, (float) $nota, null, $request->user()->id);
                        $contador++;
                    }
                }
            }
        }

        return redirect()->route('evaluations.show', [$courseSubject, $plan])
            ->with('success', 'Notas registradas ('.$contador.' calificaciones guardadas).');
    }

    /**
     * Descarga la plantilla Excel de la evaluación del trimestre (HU-48).
     */
    public function descargarPlantilla(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): StreamedResponse
    {
        $this->autorizar($request, $courseSubject);

        $contenido = app(EvaluationTemplateService::class)->exportar($plan->load('criteria.activities'));

        $nombre = 'evaluacion_trim'.$plan->trimestre.'_'.$courseSubject->id.'_'.now()->format('Ymd').'.xlsx';

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, $nombre, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Sube una plantilla Excel completada y actualiza actividades/notas (HU-48).
     */
    public function subirPlantilla(Request $request, CourseSubject $courseSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $courseSubject);

        $validated = $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        $ruta = $request->file('archivo')->storeAs('temp', 'plantilla_'.$plan->id.'.xlsx', 'local');

        try {
            $resultado = app(EvaluationTemplateService::class)->importar($plan, Storage::disk('local')->path($ruta));
        } finally {
            Storage::disk('local')->delete($ruta);
        }

        $mensaje = 'Plantilla procesada: '.$resultado['estudiantes'].' estudiantes, '.$resultado['notas'].' notas registradas.';
        if (! empty($resultado['errores'])) {
            $mensaje .= ' Errores: '.implode(' | ', array_slice($resultado['errores'], 0, 5));
        }

        return redirect()->route('evaluations.show', [$courseSubject, $plan])
            ->with($resultado['errores'] ? 'error' : 'success', $mensaje);
    }

    private function autorizar(Request $request, CourseSubject $courseSubject): void
    {
        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $teacher->id !== $courseSubject->teacher_id, 403);
    }
}
