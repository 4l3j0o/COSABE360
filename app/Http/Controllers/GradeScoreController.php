<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\GradeScore;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeScoreController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->hasRole('Docente')) {
            $assessments = Assessment::with(['subject', 'academicYear', 'courseSubject.subject'])
                ->withCount('gradeScores')
                ->orderByDesc('fecha')
                ->get();

            return view('grade-scores.index', compact('assessments'));
        }

        $bimestre = $request->input('bimestre');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();

            return $this->verCalificaciones($student, $bimestre);
        }

        if ($user->hasAnyRole('Administrador,Padre/Tutor') || $user->hasRole('Padre/Tutor')) {
            $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();

            if (! $parentGuardian) {
                if ($user->hasRole('Administrador')) {
                    $assessments = Assessment::with(['subject', 'academicYear', 'courseSubject.subject'])
                        ->withCount('gradeScores')
                        ->orderByDesc('fecha')
                        ->get();

                    return view('grade-scores.index', compact('assessments'));
                }

                return view('calificaciones.index', [
                    'estudiantes' => collect(),
                    'student' => null,
                    'calificacionesPorMateria' => collect(),
                    'promedioGeneral' => 0,
                    'bimestre' => $bimestre,
                ]);
            }

            $hijos = $parentGuardian->students()->where('students.estado', 'ACTIVO')->get();
            $studentId = $request->input('student_id');

            if ($hijos->isEmpty()) {
                return view('calificaciones.index', [
                    'estudiantes' => $hijos,
                    'student' => null,
                    'calificacionesPorMateria' => collect(),
                    'promedioGeneral' => 0,
                    'bimestre' => $bimestre,
                ]);
            }

            $student = $studentId
                ? $hijos->firstWhere('id', $studentId) ?? $hijos->first()
                : $hijos->first();

            return $this->verCalificaciones($student, $bimestre, $hijos);
        }

        abort(403);
    }

    public function show(Assessment $assessment): View
    {
        $assessment->load(['subject', 'academicYear', 'courseSubject.subject', 'courseSubject.course']);

        $students = Student::where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $notasExistentes = GradeScore::where('assessment_id', $assessment->id)
            ->get()
            ->keyBy('student_id');

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
                ['assessment_id' => $assessment->id, 'student_id' => $estudianteId],
                [
                    'nota' => $nota,
                    'observaciones' => $observaciones[$estudianteId] ?? null,
                    'registered_by_user_id' => auth()->id(),
                    'registrado_en' => now(),
                ]
            );
        }

        return redirect()->route('calificaciones.show', $assessment)
            ->with('success', 'Calificaciones registradas correctamente.');
    }

    private function verCalificaciones(Student $student, ?string $bimestre, $hijos = null): View
    {
        $query = GradeScore::where('student_id', $student->id)->with([
            'assessment.courseSubject.subject',
            'assessment.courseSubject.course.section.grade',
            'assessment.subject',
        ]);

        if ($bimestre) {
            $query->whereHas('assessment', fn ($q) => $q->where('bimestre', $bimestre));
        }

        $scores = $query->get();

        $calificacionesPorMateria = $scores
            ->groupBy(function ($s) {
                return $s->assessment?->courseSubject?->subject?->nombre
                    ?? $s->assessment?->subject?->nombre
                    ?? 'Sin materia';
            })
            ->map(function ($group, $materia) {
                $notas = $group->pluck('nota');
                $assessments = $group->map(fn ($s) => $s->assessment);
                $sumPesos = $assessments->sum('peso');
                $promedio = $sumPesos > 0
                    ? round($group->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2)
                    : round($notas->avg(), 2);

                return [
                    'materia' => $materia,
                    'notas' => $group->map(fn ($s) => [
                        'id' => $s->id,
                        'nombre' => $s->assessment->nombre,
                        'tipo' => $s->assessment->tipo,
                        'nota' => $s->nota,
                        'peso' => $s->assessment->peso,
                        'bimestre' => $s->assessment->bimestre,
                        'fecha' => $s->assessment->fecha?->format('d/m/Y'),
                        'observaciones' => $s->observaciones,
                    ]),
                    'promedio' => $promedio,
                    'cantidad_notas' => $notas->count(),
                ];
            })
            ->values();

        $promedioGeneral = $calificacionesPorMateria->count() > 0
            ? round($calificacionesPorMateria->avg('promedio'), 2)
            : 0;

        if (view()->exists('calificaciones.index')) {
            return view('calificaciones.index', [
                'student' => $student,
                'estudiantes' => $hijos,
                'calificacionesPorMateria' => $calificacionesPorMateria,
                'promedioGeneral' => $promedioGeneral,
                'bimestre' => $bimestre,
            ]);
        }

        return view('grade-scores.index', [
            'assessments' => Assessment::with(['subject', 'academicYear'])->withCount('gradeScores')->orderByDesc('fecha')->get(),
            'student' => $student,
            'calificacionesPorMateria' => $calificacionesPorMateria,
            'promedioGeneral' => $promedioGeneral,
        ]);
    }
}
