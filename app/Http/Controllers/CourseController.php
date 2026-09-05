<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Section;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * HU-40: índice jerárquico Nivel → Grado → Paralelo → Curso.
     */
    public function index()
    {
        $activo = AcademicYear::where('estado', 'ACTIVA')->first();

        $levels = Level::withCount(['grades' => fn ($q) => $q->where('estado', 'ACTIVO')])
            ->with([
                'grades' => function ($q) use ($activo) {
                    $q->where('estado', 'ACTIVO')
                        ->withCount(['sections' => fn ($s) => $s->where('estado', 'ACTIVO')])
                        ->with([
                            'sections' => function ($s) use ($activo) {
                                $s->where('estado', 'ACTIVO')
                                    ->withCount(['courses' => fn ($c) => $c->where('academic_year_id', $activo?->id)])
                                    ->with([
                                        'courses' => function ($c) use ($activo) {
                                            $c->where('academic_year_id', $activo?->id)
                                                ->withCount(['courseSubjects', 'registrations']);
                                        },
                                    ]);
                            },
                        ]);
                },
            ])
            ->where('estado', 'ACTIVO')
            ->orderBy('orden')
            ->get();

        return view('courses.index', compact('levels', 'activo'));
    }

    /**
     * Display a listing of the resource.
     */
    public function indexLegacy()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'grade_id'          => ['required', 'exists:grades,id'],
            'section_id'        => ['required', 'exists:sections,id'],
            'academic_year_id'  => ['required', 'exists:academic_years,id'],
            'aula'              => ['nullable', 'string', 'max:20'],
        ]);

        $grade = Grade::findOrFail($validated['grade_id']);
        $section = Section::findOrFail($validated['section_id']);
        $year = AcademicYear::findOrFail($validated['academic_year_id']);

        $existe = Course::where('grade_id', $grade->id)
            ->where('section_id', $section->id)
            ->where('academic_year_id', $year->id)
            ->exists();

        if ($existe) {
            return back()->withErrors(['general' => 'Ese curso (grado, paralelo y período) ya está registrado.'])->withInput();
        }

        Course::create([
            'grade_id'         => $grade->id,
            'section_id'       => $section->id,
            'academic_year_id' => $year->id,
            'nombre'           => $grade->nombre . ' "' . $section->nombre . '" - ' . $year->nombre,
            'aula'             => $validated['aula'] ?? null,
            'estado'           => 'ACTIVO',
        ]);

        return redirect()->route('asistencias.index')->with('success', 'Curso creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $course->load(['grade.level', 'section', 'gestion']);

        $cantMaterias = CourseSubject::where('course_id', $course->id)->count();
        $cantEstudiantes = Registration::where('course_id', $course->id)->where('estado', 'ACTIVA')->count();

        return view('courses.show', compact('course', 'cantMaterias', 'cantEstudiantes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'aula'   => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO,ELIMINADO'],
        ]);

        $course->update($validated);

        return redirect()->route('courses.show', $course)->with('success', 'Curso actualizado correctamente.');
    }

    public function toggle(Course $course)
    {
        $nuevo = $course->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $course->update(['estado' => $nuevo]);
        $accion = $nuevo === 'ACTIVO' ? 'activado' : 'desactivado';

        return redirect()->back()->with('success', "Curso {$accion} correctamente.");
    }

    public function destroy(Course $course)
    {
        $course->update(['estado' => 'ELIMINADO']);

        return redirect()->route('asistencias.index')->with('success', 'Curso eliminado correctamente.');
    }
}
