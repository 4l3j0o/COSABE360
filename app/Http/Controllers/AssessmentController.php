<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\CourseSubject;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    private const DIAS_SEMANA = [
        0 => 'DOMINGO',
        1 => 'LUNES',
        2 => 'MARTES',
        3 => 'MIERCOLES',
        4 => 'JUEVES',
        5 => 'VIERNES',
        6 => 'SABADO',
    ];
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        $assessments = Assessment::with(['subject', 'academicYear', 'courseSubject.course'])
            ->when($teacher, function ($q) use ($teacher) {
                $q->whereHas('courseSubject', fn ($cq) => $cq->where('teacher_id', $teacher->id));
            })
            ->when($request->query('subject_id'), fn ($q, $v) => $q->where('subject_id', $v))
            ->when($request->query('academic_year_id'), fn ($q, $v) => $q->where('academic_year_id', $v))
            ->orderByDesc('fecha')
            ->get();

        $subjects = Subject::orderBy('nombre')
            ->when($teacher, fn ($q) => $q->whereIn('id', CourseSubject::where('teacher_id', $teacher->id)->pluck('subject_id')))
            ->get();

        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('assessments.index', compact('assessments', 'subjects', 'academicYears'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('nombre')->get();
        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('assessments.create', compact('subjects', 'academicYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'string', 'max:50'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'course_subject_id' => ['nullable', 'integer', 'exists:course_subjects,id'],
            'bimestre' => ['nullable', 'integer', 'min:1', 'max:4'],
            'peso' => ['required', 'numeric', 'min:0', 'max:1'],
            'fecha' => ['nullable', 'date'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        if (empty($validated['course_subject_id'])) {
            $teacher = $request->user()->teacher;
            $validated['course_subject_id'] = $teacher
                ? CourseSubject::where('teacher_id', $teacher->id)
                    ->where('subject_id', $validated['subject_id'])
                    ->value('id')
                : null;
        }

        Assessment::create($validated);

        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación registrada correctamente.');
    }

    public function edit(Request $request, Assessment $assessment): View
    {
        $this->autorizarEvaluacion($request, $assessment);

        $subjects = Subject::orderBy('nombre')->get();
        $academicYears = AcademicYear::orderByDesc('nombre')->get();

        return view('assessments.edit', compact('assessment', 'subjects', 'academicYears'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->autorizarEvaluacion($request, $assessment);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'string', 'max:50'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'course_subject_id' => ['nullable', 'integer', 'exists:course_subjects,id'],
            'bimestre' => ['nullable', 'integer', 'min:1', 'max:4'],
            'peso' => ['required', 'numeric', 'min:0', 'max:1'],
            'fecha' => ['nullable', 'date'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $assessment->update($validated);

        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroy(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->autorizarEvaluacion($request, $assessment);

        if ($assessment->gradeScores()->exists()) {
            return redirect()->route('evaluaciones.index')->with('error', 'No se puede eliminar la evaluación porque tiene calificaciones registradas.');
        }

        $assessment->delete();

        return redirect()->route('evaluaciones.index')->with('success', 'Evaluación eliminada correctamente.');
    }

    // --- Evaluaciones por curso/paralelo (Mis Cursos) ---

    public function porCurso(Request $request, CourseSubject $courseSubject): View
    {
        $this->autorizarCurso($request, $courseSubject);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion']);

        $dias = $this->diasDeClase($courseSubject);

        $assessments = $courseSubject->assessments()
            ->withCount('gradeScores')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return view('assessments.curso', compact('courseSubject', 'assessments', 'dias'));
    }

    public function crearCurso(Request $request, CourseSubject $courseSubject): View
    {
        $this->autorizarCurso($request, $courseSubject);

        $courseSubject->load(['subject', 'course.grade', 'course.section', 'course.gestion']);

        $dias = $this->diasDeClase($courseSubject);

        return view('assessments.create-curso', compact('courseSubject', 'dias'));
    }

    public function guardarCurso(Request $request, CourseSubject $courseSubject): RedirectResponse
    {
        $this->autorizarCurso($request, $courseSubject);

        $dias = $this->diasDeClase($courseSubject);
        $diasLegibles = self::diasLegibles($dias);

        $ruleFecha = ['nullable', 'date'];

        if ($dias->isNotEmpty()) {
            $ruleFecha = ['required', 'date', function ($attribute, $value, $fail) use ($dias) {
                $dia = self::DIAS_SEMANA[Carbon::parse($value)->dayOfWeek];

                if (! $dias->contains($dia)) {
                    $fail('La fecha debe ser un día en que le toca este curso: '.self::diasLegibles($dias).'.');
                }
            }];
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'string', 'max:50'],
            'bimestre' => ['nullable', 'integer', 'min:1', 'max:4'],
            'peso' => ['required', 'numeric', 'min:1', 'max:100'],
            'fecha' => $ruleFecha,
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], $dias->isNotEmpty() ? [
            'fecha.required' => 'Debe indicar la fecha. Tiene que ser un día en que le toca este curso: '.$diasLegibles.'.',
        ] : []);

        $courseSubject->assessments()->create([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'bimestre' => $validated['bimestre'] ?? null,
            'peso' => $validated['peso'] / 100,
            'fecha' => $validated['fecha'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'subject_id' => $courseSubject->subject_id,
            'academic_year_id' => $courseSubject->course_id ? $courseSubject->course->academic_year_id : null,
        ]);

        return redirect()
            ->route('docente.evaluaciones', $courseSubject)
            ->with('success', 'Evaluación registrada para '.$courseSubject->course->nombre.'.');
    }

    private function autorizarEvaluacion(Request $request, Assessment $assessment): void
    {
        // Las evaluaciones genéricas (por materia, sin curso) se mantienen editables;
        // las ligadas a un paralelo solo para el docente titular de ese curso-materia.
        if ($assessment->course_subject_id === null) {
            return;
        }

        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $assessment->courseSubject?->teacher_id !== $teacher->id, 403);
    }

    private function autorizarCurso(Request $request, CourseSubject $courseSubject): void
    {
        $teacher = $request->user()->teacher;

        abort_if(! $teacher || $teacher->id !== $courseSubject->teacher_id, 403);
    }

    private function diasDeClase(CourseSubject $courseSubject): Collection
    {
        return $courseSubject->schedules()
            ->distinct()
            ->pluck('dia')
            ->values();
    }

    private static function diasLegibles(Collection $dias): string
    {
        return $dias->map(fn ($dia) => strtolower($dia))->implode(', ');
    }
}
