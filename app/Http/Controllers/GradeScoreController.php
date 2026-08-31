<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\GradeScore;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeScoreController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('Docente')) {
            return redirect()->route('asistencias.index');
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
                    return redirect()->route('asistencias.index');
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

    /**
     * Muestra las calificaciones de una evaluación en solo lectura (Ver).
     */
    public function ver(Assessment $assessment): View
    {
        $assessment->load(['subject', 'academicYear', 'courseSubject.course']);

        $students = Student::where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $notasExistentes = GradeScore::where('assessment_id', $assessment->id)
            ->get()
            ->keyBy('student_id');

        return view('grade-scores.ver', compact('assessment', 'students', 'notasExistentes'));
    }

    /**
     * Lista las evaluaciones de un curso concreto (vista admin, acceso desde Cursos).
     */
    public function cursoAdmin(Request $request, Course $course): View
    {
        $course->load(['grade', 'section', 'gestion']);

        $user = $request->user();
        $esDocente = $user->role?->nombre === 'Docente';
        $teacherId = $esDocente ? optional($user->teacher)->id : null;

        $courseSubjectsQuery = $course->courseSubjects()->with('subject');

        if ($esDocente) {
            $courseSubjectsQuery->where('teacher_id', $teacherId);
        }

        $materias = $courseSubjectsQuery->get()
            ->map(fn ($cs) => $cs->subject)
            ->filter()
            ->unique('id')
            ->values();

        $query = Assessment::with(['subject', 'academicYear', 'courseSubject.course'])
            ->whereHas('courseSubject', fn ($q) => $q->where('course_id', $course->id));

        if ($esDocente) {
            $query->whereHas('courseSubject', fn ($q) => $q->where('course_id', $course->id)->where('teacher_id', $teacherId));
        }

        $materia = $request->query('materia');
        $bimestre = $request->query('bimestre');

        if ($materia) {
            $query->where('subject_id', $materia);
        }
        if ($bimestre) {
            $query->where('bimestre', $bimestre);
        }

        $assessments = $query->withCount('gradeScores')->orderByDesc('fecha')->get();

        return view('grade-scores.curso', compact('assessments', 'course', 'materias', 'materia', 'bimestre'));
    }

    public function porCurso(Request $request, CourseSubject $courseSubject, Assessment $assessment): View
    {
        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $teacher->id !== $courseSubject->teacher_id, 403);
        abort_if($assessment->course_subject_id !== $courseSubject->id, 404);

        $assessment->load(['subject', 'academicYear', 'courseSubject.subject', 'courseSubject.course']);

        $students = $courseSubject->course->students()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $notasExistentes = GradeScore::where('assessment_id', $assessment->id)
            ->get()
            ->keyBy('student_id');

        $volver = route('docente.evaluaciones', $courseSubject);
        $volverCurso = $courseSubject->id;

        return view('grade-scores.show', compact('assessment', 'students', 'notasExistentes', 'volver', 'volverCurso'));
    }

    public function notasCurso(Request $request, CourseSubject $courseSubject): View
    {
        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $teacher->id !== $courseSubject->teacher_id, 403);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion']);

        $assessments = $courseSubject->assessments()
            ->orderBy('bimestre')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $students = $courseSubject->course->students()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $notas = GradeScore::whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function ($student) use ($assessments, $notas) {
            $notasEstudiante = $notas->get($student->id, collect());

            $porEvaluacion = [];
            $sumaPonderada = 0;
            $sumaPesos = 0;
            $evaluacionesConNota = 0;

            foreach ($assessments as $assessment) {
                $nota = $notasEstudiante->firstWhere('assessment_id', $assessment->id);
                $valor = $nota && $nota->nota !== null ? (float) $nota->nota : null;
                $porEvaluacion[$assessment->id] = $valor;

                if ($valor !== null) {
                    $peso = (float) $assessment->peso;
                    $sumaPonderada += $valor * $peso;
                    $sumaPesos += $peso;
                    $evaluacionesConNota++;
                }
            }

            $notaFinal = $sumaPesos > 0
                ? round($sumaPonderada / $sumaPesos, 2)
                : null;

            return [
                'codigo' => $student->codigo,
                'apellidos' => $student->apellidos,
                'nombres' => $student->nombres,
                'porEvaluacion' => $porEvaluacion,
                'notaFinal' => $notaFinal,
                'evaluacionesConNota' => $evaluacionesConNota,
                'totalEvaluaciones' => $assessments->count(),
            ];
        });

        return view('grade-scores.notas-curso', compact('courseSubject', 'assessments', 'students', 'rows'));
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

        if ($request->filled('volver_curso')) {
            $courseSubject = CourseSubject::find($request->input('volver_curso'));

            if ($courseSubject) {
                return redirect()->route('docente.evaluaciones', $courseSubject)
                    ->with('success', 'Calificaciones registradas correctamente.');
            }
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

        return view('calificaciones.index', [
            'student' => $student,
            'estudiantes' => $hijos,
            'calificacionesPorMateria' => $calificacionesPorMateria,
            'promedioGeneral' => $promedioGeneral,
            'bimestre' => $bimestre,
        ]);
    }
}
