<?php

namespace App\Services;

use App\Models\EvaluationPlan;
use App\Models\Registration;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ranking de promedios por estudiante.
 *
 * Calcula las notas de evaluación sin consultar una vez por estudiante:
 * primero agrega las notas por plan/criterio/estudiante y luego reutiliza
 * ese mapa para el promedio general o el de una materia.
 */
class RankingPromediosService
{
    /**
     * @param  Collection<int, Section>  $sections  Secciones ya autorizadas para el usuario.
     * @param  int|null  $subjectId  null = promedio general.
     * @param  string  $periodo  'anual' o '1', '2', '3'.
     */
    public function calcular(
        Collection $sections,
        ?int $subjectId,
        string $periodo,
        ?int $limite = 50
    ): array {
        $sections = $sections->values();
        $periodo = in_array($periodo, ['1', '2', '3'], true) ? $periodo : 'anual';
        $periodoKey = $periodo === 'anual' ? 'anual' : $periodo;

        $sectionIds = $sections->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($sectionIds === []) {
            return $this->resultadoVacio($periodo, $subjectId);
        }

        $studentsPorSeccion = $this->estudiantesPorSeccion($sectionIds);
        $studentIds = [];
        foreach ($studentsPorSeccion as $students) {
            foreach ($students as $student) {
                $studentIds[$student->id] = $student->id;
            }
        }

        if ($studentIds === []) {
            return $this->resultadoVacio($periodo, $subjectId);
        }

        $sectionSubjects = SectionSubject::with('subject')
            ->whereIn('section_id', $sectionIds)
            ->get()
            ->groupBy('section_id');

        $csIds = $sectionSubjects->flatten(1)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $notas = $this->notasPorMateria($csIds, array_values($studentIds), $periodo);

        $filas = [];
        $sinNota = 0;

        foreach ($sections as $section) {
            $css = $sectionSubjects->get($section->id, collect());
            $students = $studentsPorSeccion[$section->id] ?? [];
            if ($css->isEmpty() || $students === []) {
                continue;
            }

            [$hijasDe, $esHija] = $this->relacionesMaterias($css);

            if ($subjectId !== null) {
                $objetivo = $css->first(fn (SectionSubject $cs) => (int) $cs->subject_id === $subjectId);
                if (! $objetivo) {
                    continue;
                }
                $materiasARanking = collect([$objetivo]);
            } else {
                // Las materias hijas no se promedian dos veces: su nota entra
                // en la materia padre, igual que en el boletín.
                $materiasARanking = $css->reject(
                    fn (SectionSubject $cs) => isset($esHija[(int) $section->id][(int) $cs->subject_id])
                )->values();
            }

            foreach ($students as $student) {
                $notasMaterias = [];
                foreach ($materiasARanking as $cs) {
                    $nota = $this->notaMateria($cs, $student->id, $periodoKey, $notas, $hijasDe);
                    if ($nota !== null) {
                        $notasMaterias[] = $nota;
                    }
                }

                if ($notasMaterias === []) {
                    $sinNota++;
                    continue;
                }

                $promedio = (float) round(array_sum($notasMaterias) / count($notasMaterias));
                $valoracion = EvaluationService::valoracion($promedio);

                $filas[] = (object) [
                    'student' => $student,
                    'section' => $section,
                    'promedio' => $promedio,
                    'valoracion' => $valoracion,
                ];
            }
        }

        $filas = collect($filas)
            ->sortBy(fn ($fila) => $fila->student->nombres)
            ->sortBy(fn ($fila) => $fila->student->apellidos)
            ->sortByDesc(fn ($fila) => $fila->promedio)
            ->values();

        $posicion = 0;
        $promedioAnterior = null;
        foreach ($filas as $indice => $fila) {
            if ($promedioAnterior === null || $fila->promedio !== $promedioAnterior) {
                $posicion = $indice + 1;
                $promedioAnterior = $fila->promedio;
            }
            $fila->posicion = $posicion;
        }

        $totalFilas = $filas->count();
        if ($limite !== null) {
            $filas = $filas->take($limite)->values();
        }

        return [
            'filas' => $filas,
            'total_filas' => $totalFilas,
            'sin_nota' => $sinNota,
            'periodo' => $periodo,
            'subject_id' => $subjectId,
        ];
    }

    private function resultadoVacio(string $periodo, ?int $subjectId): array
    {
        return [
            'filas' => collect(),
            'total_filas' => 0,
            'sin_nota' => 0,
            'periodo' => $periodo,
            'subject_id' => $subjectId,
        ];
    }

    /**
     * @return array<int, array<int, Student>>
     */
    private function estudiantesPorSeccion(array $sectionIds): array
    {
        $registrations = Registration::query()
            ->whereIn('section_id', $sectionIds)
            ->where('estado', 'ACTIVA')
            ->whereHas('enrollment', fn ($query) => $query->where('estado', 'COMPLETADA'))
            ->with('enrollment.estudiante')
            ->get();

        $resultado = [];
        foreach ($registrations as $registration) {
            $student = $registration->enrollment?->estudiante;
            if (! $student || $student->estado !== 'ACTIVO') {
                continue;
            }
            $resultado[(int) $registration->section_id][(int) $student->id] = $student;
        }

        return $resultado;
    }

    /**
     * Devuelve notas[section_subject_id][periodo][student_id].
     *
     * @return array<int, array<string, array<int, float>>>
     */
    private function notasPorMateria(array $csIds, array $studentIds, string $periodo): array
    {
        if ($csIds === [] || $studentIds === []) {
            return [];
        }

        $trimestres = $periodo === 'anual' ? [1, 2, 3] : [(int) $periodo];

        $plans = EvaluationPlan::query()
            ->whereIn('section_subject_id', $csIds)
            ->whereIn('trimestre', $trimestres)
            ->get(['id', 'section_subject_id', 'trimestre']);

        if ($plans->isEmpty()) {
            return [];
        }

        $planIds = $plans->pluck('id')->map(fn ($id) => (int) $id)->all();
        $planMap = $plans->keyBy('id');

        $criteriosPorPlan = DB::table('evaluation_criteria')
            ->whereIn('evaluation_plan_id', $planIds)
            ->selectRaw('evaluation_plan_id, COUNT(*) AS total')
            ->groupBy('evaluation_plan_id')
            ->pluck('total', 'evaluation_plan_id');

        $notasCriterio = DB::table('evaluation_activity_grades as g')
            ->join('evaluation_activities as a', 'a.id', '=', 'g.evaluation_activity_id')
            ->join('evaluation_criteria as c', 'c.id', '=', 'a.evaluation_criteria_id')
            ->join('evaluation_plans as p', 'p.id', '=', 'c.evaluation_plan_id')
            ->whereIn('p.id', $planIds)
            ->whereIn('g.student_id', $studentIds)
            ->selectRaw('p.id AS plan_id, c.id AS criterion_id, g.student_id, AVG(g.nota) AS promedio')
            ->groupBy('p.id', 'c.id', 'g.student_id')
            ->get();

        $porPlan = [];
        foreach ($notasCriterio as $nota) {
            $porPlan[(int) $nota->plan_id][(int) $nota->student_id][(int) $nota->criterion_id] = (float) $nota->promedio;
        }

        $notasPlan = [];
        foreach ($porPlan as $planId => $students) {
            $plan = $planMap->get($planId);
            $requeridos = (int) ($criteriosPorPlan[$planId] ?? 0);
            if (! $plan || $requeridos === 0) {
                continue;
            }

            foreach ($students as $studentId => $criterios) {
                if (count($criterios) < $requeridos) {
                    continue;
                }
                $notasPlan[(int) $plan->section_subject_id][(int) $plan->trimestre][(int) $studentId] =
                    (float) array_sum(array_map(fn ($nota) => round($nota), $criterios));
            }
        }

        $resultado = [];
        foreach ($notasPlan as $csId => $trims) {
            if ($periodo !== 'anual') {
                $trimestre = (int) $periodo;
                $resultado[$csId][$periodo] = $trims[$trimestre] ?? [];
                continue;
            }

            $studentes = [];
            foreach ([1, 2, 3] as $trimestre) {
                foreach (array_keys($trims[$trimestre] ?? []) as $studentId) {
                    $studentes[$studentId] = $studentId;
                }
            }

            foreach ($studentes as $studentId) {
                $notas = [];
                for ($trimestre = 1; $trimestre <= 3; $trimestre++) {
                    $nota = $trims[$trimestre][$studentId] ?? null;
                    if ($nota === null) {
                        continue 2;
                    }
                    $notas[] = $nota;
                }
                $resultado[$csId]['anual'][$studentId] = (float) round(array_sum($notas) / 3);
            }
        }

        return $resultado;
    }

    /**
     * @return array{0: array<int, array<int, array<int, SectionSubject>>>, 1: array<int, array<int, bool>>}
     */
    private function relacionesMaterias(Collection $sectionSubjects): array
    {
        $subjectIds = $sectionSubjects->pluck('subject_id')->map(fn ($id) => (int) $id);
        $hijasDe = [];
        $esHija = [];

        foreach ($sectionSubjects as $cs) {
            $parentId = $cs->subject?->parent_id;
            if ($parentId && $subjectIds->contains((int) $parentId)) {
                $hijasDe[(int) $cs->section_id][(int) $parentId][] = $cs;
                $esHija[(int) $cs->section_id][(int) $cs->subject_id] = true;
            }
        }

        return [$hijasDe, $esHija];
    }

    private function notaMateria(
        SectionSubject $cs,
        int $studentId,
        string $periodoKey,
        array $notas,
        array $hijasDe
    ): ?float {
        $hijas = $hijasDe[(int) $cs->section_id][(int) $cs->subject_id] ?? [];

        if ($hijas !== []) {
            $notasHijas = [];
            foreach ($hijas as $hija) {
                $nota = $notas[(int) $hija->id][$periodoKey][$studentId] ?? null;
                if ($nota === null) {
                    return null;
                }
                $notasHijas[] = $nota;
            }

            return $notasHijas === [] ? null : (float) round(array_sum($notasHijas) / count($notasHijas));
        }

        return $notas[(int) $cs->id][$periodoKey][$studentId] ?? null;
    }
}
