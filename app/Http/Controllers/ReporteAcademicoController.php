<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\GradeScore;
use App\Models\Subject;
use Illuminate\Http\Request;

class ReporteAcademicoController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        $courses = Course::with(['grade', 'section'])->orderBy('nombre')->get();
        $subjects = Subject::orderBy('nombre')->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'academic_years' => $academicYears,
                'courses' => $courses,
                'subjects' => $subjects,
            ]]);
        }

        return view('reportes.academicos.index', compact('academicYears', 'courses', 'subjects'));
    }

    public function calificaciones(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'bimestre' => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $query = GradeScore::with(['assessment.subject', 'assessment.academicYear', 'assessment.courseSubject.course', 'student'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('academic_year_id', $v)))
            ->when($request->bimestre, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('bimestre', $v)))
            ->when($request->subject_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('subject_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->whereHas('assessment.courseSubject', fn ($cq) => $cq->where('course_id', $v)))
            ->orderByDesc('id');

        $result = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['data' => $result]);
        }

        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        $courses = Course::with(['grade', 'section'])->get();
        $subjects = Subject::orderBy('nombre')->get();

        return view('reportes.academicos.calificaciones', [
            'scores' => $result,
            'academicYears' => $academicYears,
            'courses' => $courses,
            'subjects' => $subjects,
            'filtros' => $request->only(['academic_year_id', 'course_id', 'subject_id', 'bimestre']),
        ]);
    }

    public function promedios(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ]);

        $query = GradeScore::with(['assessment', 'student'])
            ->when($request->academic_year_id, fn ($q, $v) => $q->whereHas('assessment', fn ($aq) => $aq->where('academic_year_id', $v)))
            ->when($request->course_id, fn ($q, $v) => $q->whereHas('assessment.courseSubject', fn ($cq) => $cq->where('course_id', $v)))
            ->get();

        $promedios = $query->groupBy('student_id')->map(function ($scores, $studentId) {
            $student = $scores->first()->student;
            $porMateria = $scores->groupBy(fn ($s) => $s->assessment->subject_id)->map(function ($mScores) {
                $subject = $mScores->first()->assessment->subject;
                $sumPesos = $mScores->sum('assessment.peso');
                $prom = $sumPesos > 0 ? round($mScores->sum(fn ($s) => $s->nota * ($s->assessment->peso / $sumPesos)), 2) : round($mScores->avg('nota'), 2);
                return ['materia' => $subject?->nombre ?? 'Sin materia', 'promedio' => $prom, 'cantidad' => $mScores->count()];
            })->values();
            $promGeneral = $porMateria->avg('promedio');
            return [
                'student_id' => $studentId,
                'estudiante' => $student ? $student->nombres . ' ' . $student->apellidos : 'N/A',
                'codigo' => $student?->codigo,
                'promedio_general' => round($promGeneral, 2),
                'por_materia' => $porMateria,
            ];
        })->values();

        if ($request->expectsJson()) {
            return response()->json(['data' => $promedios]);
        }

        $academicYears = AcademicYear::orderByDesc('nombre')->get();
        $courses = Course::with(['grade', 'section'])->get();

        return view('reportes.academicos.promedios', [
            'promedios' => $promedios,
            'academicYears' => $academicYears,
            'courses' => $courses,
            'filtros' => $request->only(['academic_year_id', 'course_id']),
        ]);
    }
}
