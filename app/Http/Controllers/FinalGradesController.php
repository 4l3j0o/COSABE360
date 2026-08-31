<?php

namespace App\Http\Controllers;

use App\Models\GradeScore;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FinalGradesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bimestre = $request->input('bimestre', '4');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            $notasFinales = $this->calcularNotas($student, $bimestre);
            return view('notas-finales.index', [
                'student' => $student,
                'estudiantes' => collect([$student]),
                'resumenEstudiantes' => collect(),
                'notasFinales' => $notasFinales,
                'bimestre' => $bimestre,
            ]);
        }

        if ($user->hasRole('Administrador') || $user->hasRole('Director')) {
            $estudiantes = $this->consultarEstudiantes($request);
            $resumen = $this->calcularResumenTodos($estudiantes->getCollection(), $bimestre);
            return view('notas-finales.index', [
                'student' => null,
                'estudiantes' => $estudiantes,
                'resumenEstudiantes' => $resumen,
                'notasFinales' => collect(),
                'bimestre' => $bimestre,
            ]);
        }

        if ($user->hasRole('Padre/Tutor')) {
            $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();
            if (!$parentGuardian) {
                return view('notas-finales.index', [
                    'student' => null,
                    'estudiantes' => collect(),
                    'resumenEstudiantes' => collect(),
                    'notasFinales' => collect(),
                    'bimestre' => $bimestre,
                ]);
            }

            $hijos = $this->consultarEstudiantes($request, $parentGuardian);
            $resumen = $this->calcularResumenTodos($hijos->getCollection(), $bimestre);
            return view('notas-finales.index', [
                'student' => null,
                'estudiantes' => $hijos,
                'resumenEstudiantes' => $resumen,
                'notasFinales' => collect(),
                'bimestre' => $bimestre,
            ]);
        }

        abort(403);
    }

    private function consultarEstudiantes(Request $request, $parentGuardian = null)
    {
        $query = Student::where('estado', 'ACTIVO');

        if ($parentGuardian) {
            $query = $parentGuardian->students()->where('students.estado', 'ACTIVO');
        }

        if ($request->filled('buscar')) {
            $buscar = addcslashes($request->buscar, '%_');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('apellidos')->paginate(10)->withQueryString();
    }

    private function calcularNotas(Student $student, string $bimestre): Collection
    {
        return GradeScore::where('student_id', $student->id)
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
    }

    private function calcularResumenTodos(Collection $estudiantes, string $bimestre): Collection
    {
        return $estudiantes->map(function ($student) use ($bimestre) {
            $notas = $this->calcularNotas($student, $bimestre);
            $aprobadas = $notas->where('aprobado', true)->count();
            $total = $notas->count();
            $promedio = $total > 0 ? round($notas->avg('promedio'), 1) : 0;

            return [
                'id' => $student->id,
                'nombres' => $student->nombres,
                'apellidos' => $student->apellidos,
                'codigo' => $student->codigo,
                'materias' => $total,
                'aprobadas' => $aprobadas,
                'promedio' => $promedio,
                'aprobado' => $total > 0 && $aprobadas === $total,
            ];
        })->values();
    }

    public function show(Request $request)
    {
        $user = auth()->user();
        $bimestre = $request->input('bimestre', '4');
        $student = null;

        if ($user->hasRole('Administrador') || $user->hasRole('Director')) {
            $student = Student::where('estado', 'ACTIVO')->find($request->input('student_id'));
        } elseif ($user->hasRole('Padre/Tutor')) {
            $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();
            $student = $parentGuardian
                ? $parentGuardian->students()->where('students.estado', 'ACTIVO')->find($request->input('student_id'))
                : null;
        } elseif ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();
        }

        if (!$student) {
            return redirect()->route('notas-finales.index');
        }

        $notasFinales = $this->calcularNotas($student, $bimestre);
        return view('notas-finales.detalle', [
            'student' => $student,
            'notasFinales' => $notasFinales,
            'bimestre' => $bimestre,
        ]);
    }
}
