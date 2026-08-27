<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['course', 'subject', 'teacher'])
            ->orderBy('dia')
            ->orderBy('hora_inicio')
            ->paginate(15);

        $courses = Course::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        return view('schedules.index', compact('schedules', 'courses', 'subjects', 'teachers'));
    }

    public function create()
    {
        $courses = Course::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        return view('schedules.create', compact('courses', 'subjects', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:courses,id',
            'materia_id' => 'required|exists:subjects,id',
            'docente_id' => 'required|exists:teachers,id',
            'dia' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:50',
        ]);

        $errorDocente = $this->validarCruceDocente($validated['docente_id'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);
        if ($errorDocente) {
            return back()->withInput()->withErrors(['cruce' => $errorDocente]);
        }

        $errorAula = $this->validarCruceAula($validated['aula'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);
        if ($errorAula) {
            return back()->withInput()->withErrors(['cruce' => $errorAula]);
        }

        Schedule::create($validated);

        return redirect()->route('schedules.index')->with('mensaje', 'Horario creado correctamente.');
    }

    private function validarCruceDocente($docenteId, $dia, $horaInicio, $horaFin)
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
            return 'El docente ya tiene una clase asignada en ese horario.';
        }

        return null;
    }

    private function validarCruceAula($aula, $dia, $horaInicio, $horaFin)
    {
        if (empty($aula)) {
            return null;
        }

        $cruce = Schedule::where('aula', $aula)
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
            return 'El aula ya está ocupada en ese horario.';
        }

        return null;
    }

    public function show(Schedule $schedule)
    {
        //
    }

    public function edit(Schedule $schedule)
    {
        //
    }

    public function update(Request $request, Schedule $schedule)
    {
        //
    }

    public function destroy(Schedule $schedule)
    {
        //
    }
}
