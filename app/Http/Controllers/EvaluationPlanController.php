<?php

namespace App\Http\Controllers;

use App\Models\SectionSubject;
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
     * Evaluaciones (planes) por trimestre de un section_subject (curso-materia-docente).
     */
    public function index(Request $request, SectionSubject $sectionSubject): View
    {
        $this->autorizar($request, $sectionSubject);

        $sectionSubject->load(['subject', 'section.grade', 'section', 'section.gestion']);

        $plans = $sectionSubject->evaluationPlans()
            ->withCount('criteria')
            ->orderBy('trimestre')
            ->get();

        return view('evaluations.index', compact('sectionSubject', 'plans'));
    }

    /**
     * Crea (si no existe) el plan de un trimestre y sus 4 criterios por defecto.
     */
    public function guardar(Request $request, SectionSubject $sectionSubject): RedirectResponse
    {
        $this->autorizar($request, $sectionSubject);

        $validated = $request->validate([
            'trimestre' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $service = app(EvaluationService::class);
        $service->obtenerOCrearPlan($sectionSubject, $validated['trimestre']);

        $plan = EvaluationPlan::where('section_subject_id', $sectionSubject->id)
            ->where('trimestre', $validated['trimestre'])
            ->first();

        return redirect()->route('evaluations.show', [$sectionSubject, $plan])
            ->with('success', 'Evaluación del trimestre '.$validated['trimestre'].' creada.');
    }

    /**
     * Detalle del plan: criterios, actividades y notas por estudiante.
     */
    public function show(Request $request, SectionSubject $sectionSubject, EvaluationPlan $plan): View
    {
        $this->autorizar($request, $sectionSubject);
        abort_unless($plan->section_subject_id === $sectionSubject->id, 404);

        $sectionSubject->load(['subject', 'section.grade', 'section', 'section.gestion']);

        $plan->load(['criteria.activities.grades', 'criteria.activities.grades.student']);
        $service = app(EvaluationService::class);

        // Estudiantes del curso (matriculados y activos)
        $students = $sectionSubject->section->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->get();

        $estado = $plan->estado;

        return view('evaluations.show', compact('sectionSubject', 'plan', 'students', 'service', 'estado'));
    }

    /**
     * Agrega una actividad a un criterio (SABER/HACER).
     */
    public function agregarActividad(Request $request, SectionSubject $sectionSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $sectionSubject);

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

        return redirect()->route('evaluations.show', [$sectionSubject, $plan])
            ->with('success', 'Actividad agregada al criterio '.$criterion->codigo.'.');
    }

    /**
     * Registra/actualiza las notas de todos los estudiantes en una o varias actividades
     * de un criterio (formato notas[activity_id][student_id] = valor).
     */
    public function guardarNotas(Request $request, SectionSubject $sectionSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $sectionSubject);

        $validated = $request->validate([
            'notas' => ['required', 'array'],
            'notas.*' => ['array'],
            'notas.*.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $service = app(EvaluationService::class);
        $contador = 0;
        $errores = [];

        // Actividades del plan indexadas por id, con el tope de su criterio (10/45/40/5)
        $actividades = collect();
        $maxPorActividad = [];
        foreach ($plan->criteria()->with('activities')->get() as $criterion) {
            foreach ($criterion->activities as $activity) {
                $actividades->put($activity->id, $activity);
                $maxPorActividad[$activity->id] = $criterion->porcentaje;
            }
        }

        // Notas por actividad (formato: notas[activity_id][student_id])
        if (isset($validated['notas']) && is_array($validated['notas'])) {
            foreach ($validated['notas'] as $activityId => $notasEstudiante) {
                $activity = $actividades->get((int) $activityId);
                $max = $maxPorActividad[(int) $activityId] ?? null;
                if (! $activity || $max === null) {
                    continue;
                }

                foreach ($notasEstudiante as $studentId => $nota) {
                    if ($nota === null || $nota === '') {
                        continue;
                    }
                    $valor = (float) $nota;
                    if ($valor < 0 || $valor > $max) {
                        $errores[] = 'Nota '.$valor.' fuera de rango (0-'.$max.') en "'.$activity->nombre.'"';

                        continue;
                    }
                    $student = Student::find($studentId);
                    if ($student) {
                        $service->registrarNota($activity, $student, $valor, null, $request->user()->id);
                        $contador++;
                    }
                }
            }
        }

        $redirect = redirect()->route('evaluations.show', [$sectionSubject, $plan]);

        if (! empty($errores)) {
            return $redirect->with('error', 'Se guardaron '.$contador.' notas, pero se omitieron valores fuera de rango: '.implode(' | ', array_slice($errores, 0, 3)).(count($errores) > 3 ? ' (+'.(count($errores) - 3).' más)' : ''));
        }

        return $redirect->with('success', 'Notas registradas ('.$contador.' calificaciones guardadas).');
    }

    /**
     * Descarga la plantilla Excel de la evaluación del trimestre (HU-48).
     */
    public function descargarPlantilla(Request $request, SectionSubject $sectionSubject, EvaluationPlan $plan): StreamedResponse
    {
        $this->autorizar($request, $sectionSubject);

        $contenido = app(EvaluationTemplateService::class)->exportar($plan->load('criteria.activities'));

        $nombre = 'evaluacion_trim'.$plan->trimestre.'_'.$sectionSubject->id.'_'.now()->format('Ymd').'.xlsx';

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, $nombre, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Sube una plantilla Excel completada y actualiza actividades/notas (HU-48).
     */
    public function subirPlantilla(Request $request, SectionSubject $sectionSubject, EvaluationPlan $plan): RedirectResponse
    {
        $this->autorizar($request, $sectionSubject);

        $validated = $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        $ruta = $request->file('archivo')->storeAs('temp', 'plantilla_'.$plan->id.'.xlsx', 'local');

        try {
            $resultado = app(EvaluationTemplateService::class)->importar($plan, Storage::disk('local')->path($ruta));
        } finally {
            Storage::disk('local')->delete($ruta);
        }

        if (! empty($resultado['formato_invalido'])) {
            return redirect()->route('evaluations.show', [$sectionSubject, $plan])
                ->with('error', 'Formato no válido: el archivo no es una plantilla de evaluación del sistema. Descargue la plantilla con el botón «Descargar (.xlsx)» y cargue las notas sobre ese archivo.');
        }

        $mensaje = 'Plantilla procesada: '.$resultado['estudiantes'].' estudiantes, '.$resultado['notas'].' notas registradas.';
        if (! empty($resultado['errores'])) {
            if ($resultado['estudiantes'] === 0) {
                $mensaje = 'No se registró ninguna nota: ningún nombre del archivo coincide con los estudiantes de este curso. Verifique que la plantilla sea de este curso y trimestre ('.count($resultado['errores']).' filas omitidas).';
            } else {
                $total = count($resultado['errores']);
                $mensaje .= ' Errores: '.implode(' | ', array_slice($resultado['errores'], 0, 3));
                if ($total > 3) {
                    $mensaje .= ' (+'.($total - 3).' más)';
                }
            }
        }

        return redirect()->route('evaluations.show', [$sectionSubject, $plan])
            ->with($resultado['errores'] ? 'error' : 'success', $mensaje);
    }

    private function autorizar(Request $request, SectionSubject $sectionSubject): void
    {
        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $teacher->id !== $sectionSubject->teacher_id, 403);
    }
}
