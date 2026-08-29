<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradeScore;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistorialAcademicoController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $academicYearId = $request->query('academic_year_id');
        $studentId = $request->query('student_id');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student) {
                abort(404, 'Estudiante no encontrado');
            }
            $historial = $this->obtenerHistorial($student, $academicYearId);
            $gestiones = $this->gestionesDelEstudiante($student);

            return view('historial.index', [
                'student' => $student,
                'estudiantes' => null,
                'historial' => $historial,
                'gestiones' => $gestiones,
                'academic_year_id' => $academicYearId,
            ]);
        }

        if ($user->hasAnyRole('Padre/Tutor')) {
            $parent = ParentGuardian::where('user_id', $user->id)->first();

            $hijos = $parent ? $parent->students()->where('students.estado', 'ACTIVO')->get() : collect();

            if ($hijos->isEmpty()) {
                return view('historial.index', [
                    'student' => null,
                    'estudiantes' => $hijos,
                    'historial' => collect(),
                    'gestiones' => collect(),
                    'academic_year_id' => $academicYearId,
                ]);
            }

            $student = $studentId ? $hijos->firstWhere('id', $studentId) ?? $hijos->first() : $hijos->first();
            $historial = $this->obtenerHistorial($student, $academicYearId);
            $gestiones = $this->gestionesDelEstudiante($student);

            return view('historial.index', [
                'student' => $student,
                'estudiantes' => $hijos,
                'historial' => $historial,
                'gestiones' => $gestiones,
                'academic_year_id' => $academicYearId,
            ]);
        }

        // Director, Administrador, Secretaría pueden consultar cualquier estudiante
        if ($user->hasAnyRole('Director,Administrador,Secretaría,Docente')) {
            $estudiantes = Student::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
            $student = $studentId ? Student::find($studentId) : $estudiantes->first();

            if (!$student) {
                return view('historial.index', [
                    'student' => null,
                    'estudiantes' => $estudiantes,
                    'historial' => collect(),
                    'gestiones' => collect(),
                    'academic_year_id' => $academicYearId,
                ]);
            }

            $historial = $this->obtenerHistorial($student, $academicYearId);
            $gestiones = $this->gestionesDelEstudiante($student);

            return view('historial.index', [
                'student' => $student,
                'estudiantes' => $estudiantes,
                'historial' => $historial,
                'gestiones' => $gestiones,
                'academic_year_id' => $academicYearId,
            ]);
        }

        abort(403);
    }

    private function obtenerHistorial(Student $student, ?string $academicYearId)
    {
        $query = GradeScore::where('student_id', $student->id)->with([
            'assessment.subject',
            'assessment.academicYear',
            'assessment.courseSubject.subject',
            'assessment.courseSubject.course.section.grade',
        ]);

        $scores = $query->get();

        // Filtrar por gestión si se pide
        if ($academicYearId) {
            $scores = $scores->filter(function ($s) use ($academicYearId) {
                $ass = $s->assessment;
                return $ass && (string) ($ass->academic_year_id ?? $ass->courseSubject?->course?->academic_year_id ?? '') === (string) $academicYearId;
            });
        }

        // Agrupar por gestión -> materia -> bimestre
        $porMateria = $scores->groupBy(function ($s) {
            $ass = $s->assessment;
            $materia = $ass?->subject?->nombre
                ?? $ass?->courseSubject?->subject?->nombre
                ?? 'Sin materia';
            $bimestre = $ass?->bimestre ?? 'S/B';
            $gestion = $ass?->academicYear?->nombre
                ?? $ass?->courseSubject?->course?->academicYear?->nombre
                ?? $ass?->academic_year_id
                ?? 'Sin gestión';
            return $gestion . '|' . $materia;
        })->map(function ($group, $key) {
            [$gestion, $materia] = explode('|', $key, 2);
            $porBimestre = $group->groupBy(function ($s) {
                $ass = $s->assessment;
                return $ass?->bimestre ?? '0';
            })->map(function ($bGroup) {
                $assessments = $bGroup->map(fn ($s) => $s->assessment);
                $sumPesos = $assessments->sum('peso');
                $promedio = $sumPesos > 0
                    ? round($bGroup->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2)
                    : round($bGroup->avg('nota'), 2);
                return [
                    'bimestre' => $bGroup->first()->assessment?->bimestre ?? 0,
                    'promedio' => $promedio,
                    'notas' => $bGroup->map(fn ($s) => [
                        'nombre' => $s->assessment->nombre,
                        'tipo' => $s->assessment->tipo,
                        'nota' => $s->nota,
                        'peso' => $s->assessment->peso,
                        'fecha' => $s->assessment->fecha?->format('d/m/Y'),
                    ])->values(),
                ];
            })->sortBy('bimestre')->values();

            $promedioMateria = $porBimestre->avg('promedio');
            return [
                'gestion' => $gestion,
                'materia' => $materia,
                'por_bimestre' => $porBimestre,
                'promedio_materia' => round($promedioMateria, 2),
                'cantidad_notas' => $group->count(),
            ];
        })->values()
        ->sortBy([['gestion', 'asc'], ['materia', 'asc']])
        ->values();

        return $porMateria;
    }

    private function gestionesDelEstudiante(Student $student)
    {
        $scores = GradeScore::where('student_id', $student->id)->with(['assessment.academicYear'])->get();

        $gestionIds = $scores->map(function ($s) {
            $ass = $s->assessment;
            return $ass?->academic_year_id;
        })->filter()->unique()->values();

        if ($gestionIds->isEmpty()) {
            return AcademicYear::orderByDesc('nombre')->get();
        }

        return AcademicYear::whereIn('id', $gestionIds)->orderByDesc('nombre')->get();
    }
}