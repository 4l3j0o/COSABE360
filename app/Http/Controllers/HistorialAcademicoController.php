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
        $gestionId = $request->query('gestion_id');
        $studentId = $request->query('student_id');

        if ($user->hasRole('Estudiante')) {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student) {
                $student = Student::where('usuario_id', $user->id)->first();
            }
            if (!$student) {
                abort(404, 'Estudiante no encontrado');
            }
            $historial = $this->obtenerHistorial($student, $gestionId);
            $gestiones = $this->gestionesDelEstudiante($student);

            return view('historial.index', [
                'student' => $student,
                'estudiantes' => null,
                'historial' => $historial,
                'gestiones' => $gestiones,
                'gestion_id' => $gestionId,
            ]);
        }

        if ($user->hasAnyRole('Padre/Tutor')) {
            $parent = ParentGuardian::where('user_id', $user->id)->first()
                ?? ParentGuardian::where('usuario_id', $user->id)->first();

            $hijos = $parent ? $parent->students()->where('students.estado', 'ACTIVO')->get() : collect();

            if ($hijos->isEmpty()) {
                return view('historial.index', [
                    'student' => null,
                    'estudiantes' => $hijos,
                    'historial' => collect(),
                    'gestiones' => collect(),
                    'gestion_id' => $gestionId,
                ]);
            }

            $student = $studentId ? $hijos->firstWhere('id', $studentId) ?? $hijos->first() : $hijos->first();
            $historial = $this->obtenerHistorial($student, $gestionId);
            $gestiones = $this->gestionesDelEstudiante($student);

            return view('historial.index', [
                'student' => $student,
                'estudiantes' => $hijos,
                'historial' => $historial,
                'gestiones' => $gestiones,
                'gestion_id' => $gestionId,
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
                    'gestion_id' => $gestionId,
                ]);
            }

            $historial = $this->obtenerHistorial($student, $gestionId);
            $gestiones = $this->gestionesDelEstudiante($student);

            return view('historial.index', [
                'student' => $student,
                'estudiantes' => $estudiantes,
                'historial' => $historial,
                'gestiones' => $gestiones,
                'gestion_id' => $gestionId,
            ]);
        }

        abort(403);
    }

    private function obtenerHistorial(Student $student, ?string $gestionId)
    {
        $query = GradeScore::where(function ($q) use ($student) {
            $q->where('student_id', $student->id)->orWhere('estudiante_id', $student->id);
        })->with([
            'assessment.subject',
            'assessment.academicYear',
            'assessment.courseSubject.subject',
            'assessment.courseSubject.course.section.grade',
            'evaluacion.subject',
            'evaluacion.academicYear',
            'evaluacion.courseSubject.subject',
        ]);

        $scores = $query->get();

        // Filtrar por gestión si se pide
        if ($gestionId) {
            $scores = $scores->filter(function ($s) use ($gestionId) {
                $ass = $s->assessment ?? $s->evaluacion;
                return $ass && (string) ($ass->gestion_id ?? $ass->courseSubject?->course?->gestion_id ?? '') === (string) $gestionId;
            });
        }

        // Agrupar por gestión -> materia -> bimestre
        $porMateria = $scores->groupBy(function ($s) {
            $ass = $s->assessment ?? $s->evaluacion;
            $materia = $ass?->subject?->nombre
                ?? $ass?->courseSubject?->subject?->nombre
                ?? 'Sin materia';
            $bimestre = $ass?->bimestre ?? 'S/B';
            $gestion = $ass?->academicYear?->nombre
                ?? $ass?->courseSubject?->course?->academicYear?->nombre
                ?? $ass?->gestion_id
                ?? 'Sin gestión';
            return $gestion . '|' . $materia;
        })->map(function ($group, $key) {
            [$gestion, $materia] = explode('|', $key, 2);
            $porBimestre = $group->groupBy(function ($s) {
                $ass = $s->assessment ?? $s->evaluacion;
                return $ass?->bimestre ?? '0';
            })->map(function ($bGroup) {
                $assessments = $bGroup->map(fn ($s) => $s->assessment ?? $s->evaluacion);
                $sumPesos = $assessments->sum('peso');
                $promedio = $sumPesos > 0
                    ? round($bGroup->sum(fn ($s) => $s->nota * (($s->assessment ?? $s->evaluacion)->peso / $sumPesos)), 2)
                    : round($bGroup->avg('nota'), 2);
                return [
                    'bimestre' => $bGroup->first()->assessment?->bimestre ?? $bGroup->first()->evaluacion?->bimestre ?? 0,
                    'promedio' => $promedio,
                    'notas' => $bGroup->map(fn ($s) => [
                        'nombre' => ($s->assessment ?? $s->evaluacion)->nombre,
                        'tipo' => ($s->assessment ?? $s->evaluacion)->tipo,
                        'nota' => $s->nota,
                        'peso' => ($s->assessment ?? $s->evaluacion)->peso,
                        'fecha' => ($s->assessment ?? $s->evaluacion)->fecha?->format('d/m/Y'),
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
        $scores = GradeScore::where(function ($q) use ($student) {
            $q->where('student_id', $student->id)->orWhere('estudiante_id', $student->id);
        })->with(['assessment.academicYear', 'evaluacion.academicYear'])->get();

        $gestionIds = $scores->map(function ($s) {
            $ass = $s->assessment ?? $s->evaluacion;
            return $ass?->gestion_id ?? $ass?->academicYear?->id;
        })->filter()->unique()->values();

        if ($gestionIds->isEmpty()) {
            return AcademicYear::orderByDesc('nombre')->get();
        }

        return AcademicYear::whereIn('id', $gestionIds)->orderByDesc('nombre')->get();
    }
}
