<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Section;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
    }
}
