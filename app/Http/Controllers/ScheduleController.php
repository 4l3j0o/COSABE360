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
        $paralelos = Course::withCount(['schedules' => function ($query) {
            $query->whereHas('subject')->whereHas('teacher');
        }])
            ->whereHas('schedules')
            ->orderBy('nombre')
            ->get();

        $courses = Course::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        return view('schedules.index', compact('paralelos', 'courses', 'subjects', 'teachers'));
    }

    public function show(Course $course)
    {
        $course->load(['grade.level', 'section', 'gestion']);

        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('course_id', $course->id)
            ->orderBy('hora_inicio')
            ->orderByRaw("FIELD(dia, 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO')")
            ->get();

        $horas = $schedules->unique(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'))
            ->values()
            ->sortBy(fn ($s) => $s->hora_inicio)
            ->values();

        $tabla = [];
        foreach ($dias as $dia) {
            $tabla[$dia] = $schedules->where('dia', $dia)->groupBy(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'));
        }

        $courses = Course::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        return view('schedules.show', compact('course', 'dias', 'horas', 'tabla', 'courses', 'subjects', 'teachers', 'schedules'));
    }

    /**
     * Lista los estudiantes matriculados (Registration ACTIVA) de un paralelo.
     */
    public function estudiantes(Course $course)
    {
        $course->load(['grade', 'section', 'gestion']);

        $estudiantes = $course->students()
            ->with(['user'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('schedules.estudiantes', compact('course', 'estudiantes'));
    }

    /**
     * HU-29: Muestra el horario de quien consulta según su rol.
     * Estudiante -> el horario de su curso matriculado activo.
     * Docente -> los horarios de sus cursos asignados.
     */
    public function miHorario()
    {
        $user = auth()->user();

        if ($user->hasRole('Estudiante')) {
            $student = $user->student;

            if (! $student) {
                abort(403, 'No se encontró un perfil de estudiante para esta cuenta.');
            }

            $course = $student->activeCourse();

            if (! $course) {
                $course = null;
                return view('schedules.mi-horario-estudiante', [
                    'sinCurso' => true,
                    'course' => null,
                    'dias' => [],
                    'horas' => collect(),
                    'tabla' => [],
                ]);
            }

            return $this->renderHorarioCurso(
                'schedules.mi-horario-estudiante',
                $course,
                compact('student')
            );
        }

        if ($user->hasRole('Docente')) {
            $teacher = $user->teacher;

            if (! $teacher) {
                abort(403, 'No se encontró un perfil de docente para esta cuenta.');
            }

            // Horario semanal consolidado del docente (todas sus clases en una sola tabla)
            $schedules = $teacher->schedules()
                ->with(['course.grade', 'course.section', 'course.gestion', 'subject'])
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

            $horas = $schedules->unique(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'))
                ->values()
                ->sortBy(fn ($s) => $s->hora_inicio)
                ->values();

            $tabla = [];
            foreach ($dias as $dia) {
                $tabla[$dia] = $schedules->where('dia', $dia)->groupBy(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'));
            }

            $gestiones = $schedules->map(fn ($s) => $s->course?->gestion?->nombre)
                ->filter()
                ->unique()
                ->values();

            return view('schedules.mi-horario-docente', compact('teacher', 'dias', 'horas', 'tabla', 'schedules', 'gestiones'));
        }

        abort(403);
    }

    private function renderHorarioCurso(string $vista, Course $course, array $extra = [])
    {
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('course_id', $course->id)
            ->orderBy('hora_inicio')
            ->orderByRaw("FIELD(dia, 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO')")
            ->get();

        $horas = $schedules->unique(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'))
            ->values()
            ->sortBy(fn ($s) => $s->hora_inicio)
            ->values();

        $tabla = $this->tablaSemanal($schedules);

        $course->load(['grade.level', 'section', 'gestion']);

        return view($vista, array_merge($extra, compact('course', 'dias', 'horas', 'tabla', 'schedules')));
    }

    private function tablaSemanal($schedules): array
    {
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $tabla = [];
        foreach ($dias as $dia) {
            $tabla[$dia] = $schedules->where('dia', $dia)->groupBy(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'));
        }

        return $tabla;
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
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'dia' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:50',
        ]);

        $errorDocente = $this->validarCruceDocente($validated['teacher_id'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);
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
        $cruce = Schedule::where('teacher_id', $docenteId)
            ->where('dia', $dia)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where(function ($q) use ($horaInicio) {
                    $q->where('hora_inicio', '<=', $horaInicio)
                        ->where('hora_fin', '>', $horaInicio);
                })->orWhere(function ($q) use ($horaFin) {
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
                $query->where(function ($q) use ($horaInicio) {
                    $q->where('hora_inicio', '<=', $horaInicio)
                        ->where('hora_fin', '>', $horaInicio);
                })->orWhere(function ($q) use ($horaFin) {
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
