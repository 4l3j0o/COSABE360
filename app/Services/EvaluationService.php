<?php

namespace App\Services;

use App\Models\SectionSubject;
use App\Models\EvaluationActivity;
use App\Models\EvaluationActivityGrade;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationPlan;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Cálculo de evaluación según R.M. 473/2021 / R.M. 001/2022 (Ministerio de Educación de Bolivia)
 * y el formato usado por el colegio (plantilla Excel).
 *
 * Reglas:
 *  - Una evaluación de trimestre (EvaluationPlan) pertenece a un section_subject (curso-materia-docente).
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
     * Crea (si no existe) el plan de evaluación de un trimestre para un section_subject
     * y sus 4 criterios por defecto.
     */
    public function obtenerOCrearPlan(SectionSubject $sectionSubject, int $trimestre, ?string $nombre = null): EvaluationPlan
    {
        $plan = EvaluationPlan::firstOrCreate(
            ['section_subject_id' => $sectionSubject->id, 'trimestre' => $trimestre],
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
     * Promedio de las actividades que tengan nota registrada; las actividades
     * aún sin calificar se ignoran, igual que AVERAGE en la planilla oficial.
     * El promedio se redondea al entero (coincide con la plantilla del colegio).
     * Devuelve null si no hay ninguna nota registrada.
     */
    public function notaCriterio(EvaluationCriterion $criterion, Student $student): ?float
    {
        $notas = $criterion->activities()->get()
            ->map(fn (EvaluationActivity $activity) => $activity->notaDe($student))
            ->filter(fn ($nota) => $nota !== null);

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
     * Promedio final anual de un section_subject para un estudiante:
     * promedio de las notas de los 3 trimestres, redondeado a entero.
     */
    public function promedioAnual(SectionSubject $sectionSubject, Student $student): ?float
    {
        $notas = [];
        for ($t = 1; $t <= 3; $t++) {
            $plan = EvaluationPlan::where('section_subject_id', $sectionSubject->id)
                ->where('trimestre', $t)
                ->with('criteria.activities')
                ->first();

            if (! $plan) {
                continue;
            }

            $nota = $this->notaTrimestre($plan, $student);
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
