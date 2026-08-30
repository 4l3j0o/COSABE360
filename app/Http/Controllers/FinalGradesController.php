<?php

namespace App\Http\Controllers;

use App\Models\GradeScore;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;

class FinalGradesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bimestre = $request->input('bimestre', '4');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            return $this->mostrarNotasFinales($student, $bimestre);
        }

        if ($user->hasAnyRole('Administrador,Padre/Tutor')) {
            $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();
            if (!$parentGuardian) {
                return view('notas-finales.index', ['student' => null, 'notasFinales' => collect(), 'bimestre' => $bimestre]);
            }

            $hijos = $parentGuardian->students()->where('students.estado', 'ACTIVO')->get();
            $studentId = $request->input('student_id');
            $student = $studentId
                ? $hijos->firstWhere('id', $studentId) ?? $hijos->first()
                : $hijos->first();

            if (!$student) {
                return view('notas-finales.index', ['student' => null, 'estudiantes' => $hijos, 'notasFinales' => collect(), 'bimestre' => $bimestre]);
            }

            return $this->mostrarNotasFinales($student, $bimestre, $hijos);
        }

        abort(403);
    }

    private function mostrarNotasFinales(Student $student, string $bimestre, $hijos = null)
    {
        $notasFinales = GradeScore::where('student_id', $student->id)
            ->whereHas('assessment', fn ($q) => $q->where('bimestre', $bimestre))
            ->with('assessment.courseSubject.subject')
            ->get()
            ->groupBy('assessment.courseSubject.subject.nombre')
            ->map(function ($scores, $materia) {
                $sumPesos = $scores->sum('assessment.peso');
                $promedio = $sumPesos > 0
                    ? round($scores->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2)
                    : 0;

                return [
                    'materia' => $materia,
                    'promedio' => $promedio,
                    'cantidad_evaluaciones' => $scores->count(),
                    'aprobado' => $promedio >= 51,
                ];
            })
            ->values();

        return view('notas-finales.index', [
            'student' => $student,
            'estudiantes' => $hijos,
            'notasFinales' => $notasFinales,
            'bimestre' => $bimestre,
        ]);
    }
}
