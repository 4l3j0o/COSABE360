<?php

namespace App\Services;

use App\Models\CourseSubject;
use App\Models\EvaluationActivity;
use App\Models\EvaluationActivityGrade;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationPlan;
use App\Models\EvaluationSecondAttempt;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Cálculo de evaluación según R.M. 473/2021 / R.M. 001/2022 (Ministerio de Educación de Bolivia)
 * y el formato usado por el colegio (plantilla Excel).
 *
 * Reglas:
 *  - Una evaluación de trimestre (EvaluationPlan) pertenece a un course_subject (curso-materia-docente).
 *  - Tiene 4 criterios: SE(10), SA(45), HA(40), AU(5). Total 100.
 *  - Cada criterio se califica sobre su máximo = su porcentaje.
 *      * VALOR_UNICO (SER, AUTOEVALUACIÓN): una sola nota de 0..porcentaje.
 *      * ACTIVIDADES (SABER, HACER): varias actividades; el promedio simple de sus notas = nota del criterio.
 *  - Nota del trimestre = SE + SA + HA + AU (sobre 100).
 *  - Promedio final anual = (Trim1 + Trim2 + Trim3) / 3, redondeado.
 *  - Escala de valoración: ED <=50 (retenido); DA 51-68; DO 69-84; DP 85-100 (promovido). Mínimo 51.
 */
class EvaluationService
{
    /**
     * Crea (si no existe) el plan de evaluación de un trimestre para un course_subject
     * y sus 4 criterios por defecto.
     */
    public function obtenerOCrearPlan(CourseSubject $courseSubject, int $trimestre, ?string $nombre = null): EvaluationPlan
    {
        $plan = EvaluationPlan::firstOrCreate(
            ['course_subject_id' => $courseSubject->id, 'trimestre' => $trimestre],
            [
                'nombre' => $nombre ?? 'TRIM '.$trimestre,
                'estado' => 'ACTIVO',
            ]
        );

        if ($plan->criteria()->count() === 0) {
            $plan->crearCriteriosPorDefecto();
        }

        return $plan->load('criteria');
    }

    /**
     * Crea una actividad dentro de un criterio (SABER/HACER) o, en VALOR_UNICO,
     * usa el propio criterio como contenedor de la nota única.
     */
    public function crearActividad(EvaluationCriterion $criterion, string $nombre, ?string $fecha = null, int $orden = 1): EvaluationActivity
    {
        return EvaluationActivity::create([
            'evaluation_criteria_id' => $criterion->id,
            'nombre' => $nombre,
            'orden' => $orden,
            'fecha' => $fecha,
        ]);
    }

    /**
     * Registra o actualiza la nota de un estudiante en una actividad.
     */
    public function registrarNota(EvaluationActivity $activity, Student $student, float $nota, ?string $observaciones = null, ?int $userId = null): EvaluationActivityGrade
    {
        return EvaluationActivityGrade::updateOrCreate(
            ['evaluation_activity_id' => $activity->id, 'student_id' => $student->id],
            [
                'nota' => round($nota, 2),
                'observaciones' => $observaciones,
                'registered_by_user_id' => $userId ?? auth()->id(),
            ]
        );
    }

    /**
     * Nota de un estudiante en un criterio (0..porcentaje del criterio).
     * - VALOR_UNICO: usa la (única) actividad del criterio.
     * - ACTIVIDADES: promedio simple de las actividades con nota.
     * El promedio del criterio se redondea al entero (coincide con la plantilla del colegio).
     * Devuelve null si no hay ninguna nota.
     */
    public function notaCriterio(EvaluationCriterion $criterion, Student $student): ?float
    {
        $activities = $criterion->activities()->get();

        if ($activities->isEmpty()) {
            return null;
        }

        if ($criterion->tipo === 'VALOR_UNICO') {
            $nota = $activities->first()->notaDe($student);

            return $nota === null ? null : (float) round((float) $nota);
        }

        // ACTIVIDADES: promedio simple de las notas registradas, redondeado al entero
        $notas = $activities->map(fn (EvaluationActivity $a) => $a->notaDe($student))
            ->filter(fn ($n) => $n !== null)
            ->values();

        if ($notas->isEmpty()) {
            return null;
        }

        return (float) round((float) $notas->avg());
    }

    /**
     * Nota del trimestre = suma de las notas de los 4 criterios (sobre 100).
     * Devuelve null si falta algún criterio o estudiante sin notas.
     */
    public function notaTrimestre(EvaluationPlan $plan, Student $student): ?float
    {
        $plan->load('criteria.activities');

        $total = 0.0;
        $completos = true;

        foreach ($plan->criteria as $criterion) {
            $nota = $this->notaCriterio($criterion, $student);
            if ($nota === null) {
                $completos = false;

                continue;
            }
            $total += $nota;
        }

        if (! $completos) {
            return null; // requiere todos los criterios
        }

        return (float) $total; // suma de enteros (criterios) -> nota de trimestre 0-100
    }

    /**
     * Nota final del trimestre considerando la segunda instancia (R.M. 473/2021).
     * Si el estudiante aprobó la recuperación (>=51), se usa la nota de recuperación;
     * de lo contrario se mantiene la nota original del trimestre.
     */
    public function notaFinalTrimestre(EvaluationPlan $plan, Student $student): ?float
    {
        $notaOriginal = $this->notaTrimestre($plan, $student);
        if ($notaOriginal === null) {
            return null;
        }

        $segunda = EvaluationSecondAttempt::where('evaluation_plan_id', $plan->id)
            ->where('student_id', $student->id)
            ->first();

        if ($segunda && $segunda->nota_recuperacion !== null && $segunda->nota_recuperacion >= 51) {
            return (float) round((float) $segunda->nota_recuperacion);
        }

        return $notaOriginal;
    }

    /**
     * Lista los estudiantes reprobados (nota trimestre < 51) que tienen derecho a segunda instancia.
     */
    public function reprobadosParaSegundaInstancia(EvaluationPlan $plan): Collection
    {
        $plan->load('criteria.activities');
        $students = $plan->courseSubject->course->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('students.apellidos')
            ->get();

        return $students->filter(function (Student $student) use ($plan) {
            $nota = $this->notaTrimestre($plan, $student);

            return $nota !== null && $nota < 51;
        })->values();
    }

    /**
     * Habilita (o actualiza) el registro de segunda instancia de un estudiante reprobado.
     */
    public function habilitarSegundaInstancia(EvaluationPlan $plan, Student $student, ?int $userId = null): EvaluationSecondAttempt
    {
        $notaOriginal = $this->notaTrimestre($plan, $student);

        return EvaluationSecondAttempt::updateOrCreate(
            ['evaluation_plan_id' => $plan->id, 'student_id' => $student->id],
            [
                'nota_original' => $notaOriginal,
                'estado' => 'HABILITADO',
                'registered_by_user_id' => $userId ?? auth()->id(),
            ]
        );
    }

    /**
     * Registra la nota de recuperación de la segunda instancia y recalcula el estado.
     */
    public function registrarSegundaInstancia(EvaluationSecondAttempt $attempt, float $nota, ?string $fecha = null, ?int $userId = null): EvaluationSecondAttempt
    {
        $aprobado = $nota >= 51;

        $attempt->update([
            'nota_recuperacion' => round($nota, 2),
            'estado' => $aprobado ? 'APROBADO' : 'REPROBADO',
            'fecha' => $fecha ?? now()->toDateString(),
            'registered_by_user_id' => $userId ?? auth()->id(),
        ]);

        return $attempt;
    }

    /**
     * Promedio final anual de un course_subject para un estudiante:
     * promedio de las notas finales (con segunda instancia) de los 3 trimestres, redondeado a entero.
     */
    public function promedioAnual(CourseSubject $courseSubject, Student $student): ?float
    {
        $notas = [];
        for ($t = 1; $t <= 3; $t++) {
            $plan = EvaluationPlan::where('course_subject_id', $courseSubject->id)
                ->where('trimestre', $t)
                ->with('criteria.activities')
                ->first();

            if (! $plan) {
                continue;
            }

            $nota = $this->notaFinalTrimestre($plan, $student);
            if ($nota !== null) {
                $notas[] = $nota;
            }
        }

        if (count($notas) < 3) {
            return null; // requiere los 3 trimestres
        }

        return (float) round(array_sum($notas) / 3);
    }

    /**
     * Clasifica una nota (0-100) según la escala oficial.
     * Retorna [codigo, nombre, promovido].
     */
    public static function valoracion(float $nota): array
    {
        if ($nota <= 50) {
            return ['codigo' => 'ED', 'nombre' => 'En Desarrollo', 'promovido' => false];
        }
        if ($nota <= 68) {
            return ['codigo' => 'DA', 'nombre' => 'Desarrollo Aceptable', 'promovido' => true];
        }
        if ($nota <= 84) {
            return ['codigo' => 'DO', 'nombre' => 'Desarrollo Óptimo', 'promovido' => true];
        }

        return ['codigo' => 'DP', 'nombre' => 'Desarrollo Pleno', 'promovido' => true];
    }

    public static function aprobado(float $nota): bool
    {
        return $nota >= 51;
    }
}
