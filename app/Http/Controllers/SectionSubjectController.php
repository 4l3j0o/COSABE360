<?php

namespace App\Http\Controllers;

use App\Models\SectionSubject;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionSubjectController extends Controller
{
    public function index()
    {
        $sectionSubjects = SectionSubject::with(['teacher', 'subject', 'section', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('section_subjects.index', compact('sectionSubjects'));
    }

    public function create()
    {
        $teachers = Teacher::all();
        $subjects = Subject::all();
        $sections = Section::all();

        return view('section_subjects.create', compact('teachers', 'subjects', 'sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'dia' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $sectionSubject = SectionSubject::create([
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $validated['subject_id'],
                'section_id' => $validated['section_id'],
            ]);

            $this->validarCruceHorario($validated['teacher_id'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);

            Schedule::create([
                'section_id' => $validated['section_id'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => $validated['teacher_id'],
                'dia' => $validated['dia'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'aula' => $validated['aula'],
            ]);

            DB::commit();

            return redirect()->route('section_subjects.index')->with('mensaje', 'Asignación creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    private function validarCruceHorario($docenteId, $dia, $horaInicio, $horaFin)
    {
        $cruce = Schedule::where('teacher_id', $docenteId)
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

    public function show(SectionSubject $sectionSubject)
    {
        //
    }

    public function edit(SectionSubject $sectionSubject)
    {
        //
    }

    public function update(Request $request, SectionSubject $sectionSubject)
    {
        //
    }

    public function destroy(SectionSubject $sectionSubject)
    {
        //
    }
}
