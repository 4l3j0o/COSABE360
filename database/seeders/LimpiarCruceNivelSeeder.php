<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\CourseSubject;
use App\Models\EvaluationPlan;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

/**
 * HU-59: limpia asignaciones con nivel cruzado (el seeder viejo asignaba por
 * nombre sin filtrar nivel, ej. SOC de Primaria en curso de Secundaria).
 *
 * - course_subjects: fusiona la fila cruzada en la fila correcta del mismo
 *   docente y curso (misma sigla primero; si no, única correcta); mueve
 *   evaluaciones y planes. Sin candidato claro se conserva y se reporta.
 * - attendances: remapea al subject correcto del mismo curso (mismo docente
 *   que registró, única opción; misma sigla primero).
 * - assessments/schedules: remapeo directo por sigla (hoy hay 0).
 * - Idempotente.
 */
class LimpiarCruceNivelSeeder extends Seeder
{
    public array $stats = ['cs_fusionados' => 0, 'at_movidas' => 0, 'pendientes' => []];

    public function run(): void
    {
        $this->limpiarAsignaciones();
        $this->limpiarAsistencias();
        $this->limpiarDirectas();

        $this->command->info("Cruce de nivel: {$this->stats['cs_fusionados']} asignaciones fusionadas, {$this->stats['at_movidas']} asistencias movidas.");
        foreach ($this->stats['pendientes'] as $msg) {
            $this->command->warn($msg);
        }
    }

    private function nivelCurso($courseSubject): ?int
    {
        return $courseSubject->course?->grade?->level_id;
    }

    private function limpiarAsignaciones(): void
    {
        foreach (CourseSubject::with(['subject', 'course.grade.level', 'course.section', 'teacher'])->get() as $cs) {
            $nivelCurso = $this->nivelCurso($cs);
            if (! $nivelCurso || $cs->subject->level_id === $nivelCurso) {
                continue;
            }

            $candidatas = CourseSubject::with('subject')
                ->where('course_id', $cs->course_id)
                ->where('teacher_id', $cs->teacher_id)
                ->where('id', '!=', $cs->id)
                ->get()
                ->filter(fn ($c) => $c->subject->level_id === $nivelCurso)
                ->values();

            $destino = $candidatas->first(fn ($c) => $c->subject->sigla === $cs->subject->sigla)
                ?? ($candidatas->count() === 1 ? $candidatas->first() : null);

            if (! $destino) {
                $this->stats['pendientes'][] = "CS {$cs->id}: {$cs->course->section->codigo} {$cs->subject->nombre} ({$cs->teacher->apellidos}) sin destino claro, se conserva.";

                continue;
            }

            Assessment::where('course_subject_id', $cs->id)->update([
                'course_subject_id' => $destino->id,
                'subject_id' => $destino->subject_id,
            ]);
            EvaluationPlan::where('course_subject_id', $cs->id)->update(['course_subject_id' => $destino->id]);
            $cs->delete();
            $this->stats['cs_fusionados']++;
        }
    }

    private function limpiarAsistencias(): void
    {
        Attendance::with(['subject', 'course.grade.level', 'course.section'])->chunk(500, function ($rows) {
            foreach ($rows as $at) {
                $nivelCurso = $at->course?->grade?->level_id;
                if (! $nivelCurso || ! $at->subject || $at->subject->level_id === $nivelCurso) {
                    continue;
                }

                $candidatas = CourseSubject::with('subject')
                    ->where('course_id', $at->course_id)
                    ->when($at->registered_by_user_id, function ($q) use ($at) {
                        $q->whereHas('teacher', fn ($t) => $t->where('user_id', $at->registered_by_user_id));
                    })
                    ->get()
                    ->filter(fn ($c) => $c->subject->level_id === $nivelCurso)
                    ->values();

                if ($at->registered_by_user_id && $candidatas->isEmpty()) {
                    // La registró otro perfil: buscar sin filtro de docente
                    $candidatas = CourseSubject::with('subject')
                        ->where('course_id', $at->course_id)
                        ->get()
                        ->filter(fn ($c) => $c->subject->level_id === $nivelCurso)
                        ->values();
                }

                $destino = $candidatas->first(fn ($c) => $c->subject->sigla === $at->subject->sigla)
                    ?? ($candidatas->count() === 1 ? $candidatas->first() : null);

                if (! $destino) {
                    $this->stats['pendientes'][] = "AT {$at->id}: curso {$at->course->section->codigo} subject {$at->subject->nombre} sin destino claro, se conserva.";

                    continue;
                }

                $at->update(['subject_id' => $destino->subject_id]);
                $this->stats['at_movidas']++;
            }
        });
    }

    private function limpiarDirectas(): void
    {
        foreach (Assessment::with(['subject', 'courseSubject.course.grade.level'])->get() as $row) {
            $this->remapearDirecta($row, $row->courseSubject?->course);
        }
        foreach (Schedule::with(['subject', 'course.grade.level'])->get() as $row) {
            $this->remapearDirecta($row, $row->course);
        }
    }

    private function remapearDirecta($row, $course): void
    {
        $nivelCurso = $course?->grade?->level_id;
        if (! $nivelCurso || ! $row->subject || $row->subject->level_id === $nivelCurso) {
            return;
        }

        $destino = \App\Models\Subject::where('level_id', $nivelCurso)
            ->where('sigla', $row->subject->sigla)
            ->first();

        if (! $destino) {
            $this->stats['pendientes'][] = get_class($row) . " {$row->id} sin sigla equivalente, se conserva.";

            return;
        }

        $row->update(['subject_id' => $destino->id]);
        $this->stats['at_movidas']++;
    }
}
