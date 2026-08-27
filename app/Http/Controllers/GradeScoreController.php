<?php

namespace App\Http\Controllers;

use App\Models\GradeScore;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeScoreController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bimestre = $request->input('bimestre');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            return $this->verCalificaciones($student, $bimestre);
        }

        if ($user->hasAnyRole('Administrador,Padre/Tutor')) {
            $parentGuardian = ParentGuardian::where('usuario_id', $user->id)->first();

            if (!$parentGuardian) {
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

    private function verCalificaciones(Student $student, ?string $bimestre, $hijos = null)
    {
        $query = GradeScore::where('student_id', $student->id)
            ->with([
                'assessment.courseSubject.subject',
                'assessment.courseSubject.course.section.grade',
            ]);

        if ($bimestre) {
            $query->whereHas('assessment', fn ($q) => $q->where('bimestre', $bimestre));
        }

        $scores = $query->get();

        $calificacionesPorMateria = $scores
            ->groupBy('assessment.courseSubject.subject.nombre')
            ->map(function ($group, $materia) {
                $notas = $group->pluck('nota');
                $sumPesos = $group->sum('assessment.peso');
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
