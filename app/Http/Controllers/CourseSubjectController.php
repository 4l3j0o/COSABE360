<?php

namespace App\Http\Controllers;

use App\Models\CourseSubject;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseSubjectController extends Controller
{
    public function index()
    {
        $courseSubjects = CourseSubject::with(['teacher', 'subject', 'course', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('course_subjects.index', compact('courseSubjects'));
    }

    public function create()
    {
        $teachers = Teacher::all();
        $subjects = Subject::all();
        $courses = Course::all();

        return view('course_subjects.create', compact('teachers', 'subjects', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'docente_id' => 'required|exists:teachers,id',
            'materia_id' => 'required|exists:subjects,id',
            'curso_id' => 'required|exists:courses,id',
            'dia' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $courseSubject = CourseSubject::create([
                'docente_id' => $validated['docente_id'],
                'materia_id' => $validated['materia_id'],
                'curso_id' => $validated['curso_id'],
            ]);

            $this->validarCruceHorario($validated['docente_id'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);

            Schedule::create([
                'curso_id' => $validated['curso_id'],
                'materia_id' => $validated['materia_id'],
                'docente_id' => $validated['docente_id'],
                'dia' => $validated['dia'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'aula' => $validated['aula'],
            ]);

            DB::commit();

            return redirect()->route('course_subjects.index')->with('mensaje', 'Asignación creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    private function validarCruceHorario($docenteId, $dia, $horaInicio, $horaFin)
    {
        $cruce = Schedule::where('docente_id', $docenteId)
            ->where('dia', $dia)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '<=', $horaInicio)
                      ->where('hora_fin', '>', $horaInicio);
                })->orWhere(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin)
                      ->where('hora_fin', '>=', $horaFin);
                })->orWhere(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '>=', $horaInicio)
                      ->where('hora_fin', '<=', $horaFin);
                });
            })
            ->exists();

        if ($cruce) {
            throw new \Exception('El docente ya tiene un horario asignado que se cruza con el nuevo horario propuesto.');
        }
    }

    public function show(CourseSubject $courseSubject)
    {
        //
    }

    public function edit(CourseSubject $courseSubject)
    {
        //
    }

    public function update(Request $request, CourseSubject $courseSubject)
    {
        //
    }

    public function destroy(CourseSubject $courseSubject)
    {
        //
    }
}
