<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SectionSubject;
use App\Models\Registration;
use App\Models\Student;
use App\Services\EvaluationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistorialAcademicoController extends Controller
{
    /**
     * Historial académico por trimestres (sistema de evaluación vigente).
     * Estudiante: solo el propio. Docente: estudiantes de sus cursos.
     * Director/Administrador/Secretaría: cualquier estudiante.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->puede(['historial.ver', 'historial.ver.propio']), 403);

        $user = auth()->user();
        $academicYearId = $request->query('academic_year_id');
        $studentId = $request->query('student_id');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->first();
            if (! $student) {
                abort(404, 'Estudiante no encontrado');
            }

            return $this->vista($student, null, $academicYearId);
        }

        if ($user->hasRole('Docente') && $user->teacher) {
            $cursoIds = SectionSubject::where('teacher_id', $user->teacher->id)->pluck('section_id')->unique();
            $estudiantes = Student::where('estado', 'ACTIVO')
                ->whereHas('enrollments.registration', fn ($q) => $q->whereIn('section_id', $cursoIds)->where('registrations.estado', 'ACTIVA'))
                ->orderBy('apellidos')->orderBy('nombres')->get();
            $student = $studentId ? $estudiantes->firstWhere('id', (int) $studentId) : $estudiantes->first();

            return $this->vista($student, $estudiantes, $academicYearId);
        }

        // Director, Administrador, Secretaría pueden consultar cualquier estudiante
        if ($user->hasAnyRole('Director,Administrador,Secretaría')) {
            $estudiantes = Student::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
            $student = $studentId ? Student::find($studentId) : $estudiantes->first();

            return $this->vista($student, $estudiantes, $academicYearId);
        }

        abort(403);
    }

    private function vista(?Student $student, $estudiantes, ?string $academicYearId): View
    {
        if (! $student) {
            return view('historial.index', [
                'student' => null,
                'estudiantes' => $estudiantes,
                'historial' => collect(),
                'gestiones' => collect(),
                'academic_year_id' => $academicYearId,
            ]);
        }

        return view('historial.index', [
            'student' => $student,
            'estudiantes' => $estudiantes,
            'historial' => $this->obtenerHistorial($student, $academicYearId),
            'gestiones' => $this->gestionesDelEstudiante($student),
            'academic_year_id' => $academicYearId,
        ]);
    }

    /**
     * Notas por gestión -> materia -> trimestre + promedio anual y valoración oficial.
     */
    private function obtenerHistorial(Student $student, ?string $academicYearId)
    {
        $service = app(EvaluationService::class);

        $regs = Registration::where('estado', 'ACTIVA')
            ->whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id)->where('estado', 'COMPLETADA'))
            ->with('section.gestion', 'section.grade', 'section')
            ->get();

        if ($academicYearId) {
            $regs = $regs->filter(fn ($r) => (string) $r->section->academic_year_id === (string) $academicYearId)->values();
        }

        return $regs
            ->groupBy(fn ($r) => $r->section->gestion?->nombre ?? 'Sin gestión')
            ->map(function ($regsGestion, $gestion) use ($service, $student) {
                $materias = $regsGestion
                    ->flatMap(fn ($r) => $r->section->sectionSubjects()->with(['subject', 'evaluationPlans'])->get())
                    ->groupBy(fn ($cs) => $cs->subject?->nombre ?? 'Sin materia')
                    ->map(function ($css, $materia) use ($service, $student) {
                        $trimestres = [];
                        foreach ([1, 2, 3] as $t) {
                            $notas = [];
                            foreach ($css as $cs) {
                                $plan = $cs->evaluationPlans->firstWhere('trimestre', $t);
                                if ($plan) {
                                    $nota = $service->notaTrimestre($plan, $student);
                                    if ($nota !== null) {
                                        $notas[] = $nota;
                                    }
                                }
                            }
                            $trimestres[$t] = $notas ? (int) round(array_sum($notas) / count($notas)) : null;
                        }

                        $validos = array_filter($trimestres, fn ($v) => $v !== null);
                        $anual = count($validos) === 3 ? (int) round(array_sum($validos) / 3) : null;

                        return [
                            'materia' => $materia,
                            'trimestres' => $trimestres,
                            'anual' => $anual,
                            'valoracion' => $anual !== null ? EvaluationService::valoracion($anual) : null,
                        ];
                    })->values();

                return ['gestion' => $gestion, 'materias' => $materias];
            })->values()
            ->sortBy('gestion')->values();
    }

    private function gestionesDelEstudiante(Student $student)
    {
        $ids = Registration::where('estado', 'ACTIVA')
            ->whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id)->where('estado', 'COMPLETADA'))
            ->with('section')
            ->get()
            ->map(fn ($r) => $r->section->academic_year_id)
            ->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return AcademicYear::orderByDesc('nombre')->get();
        }

        return AcademicYear::whereIn('id', $ids)->orderByDesc('nombre')->get();
    }
}
