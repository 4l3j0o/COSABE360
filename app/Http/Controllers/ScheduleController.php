<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $paralelos = Course::withCount(['schedules' => function ($query) {
            $query->where('estado', 'ACTIVO')->whereHas('subject')->whereHas('teacher');
        }])
            ->ordenadoPorNivel()
            ->paginate(10)
            ->withQueryString();

        $cursosSinHorario = Course::withCount(['schedules' => function ($query) {
            $query->where('estado', 'ACTIVO')->whereHas('subject')->whereHas('teacher');
        }])
            ->ordenadoPorNivel()
            ->get()
            ->filter(fn ($c) => $c->schedules_count === 0)
            ->values();

        return view('schedules.index', compact('paralelos', 'cursosSinHorario'));
    }

    /**
     * Inicia el horario de un paralelo que aún no tiene clases y lo lleva
     * a su pantalla para asignar las clases.
     */
    public function iniciar(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        return redirect()
            ->route('schedules.show', $validated['course_id'])
            ->with('mensaje', 'Horario iniciado. Ahora asigna las clases del paralelo.');
    }

    public function show(Course $course)
    {
        $course->load(['grade.level', 'section', 'gestion']);

        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('course_id', $course->id)
            ->where('estado', 'ACTIVO')
            ->orderBy('hora_inicio')
            ->orderByRaw("CASE dia WHEN 'LUNES' THEN 1 WHEN 'MARTES' THEN 2 WHEN 'MIERCOLES' THEN 3 WHEN 'JUEVES' THEN 4 WHEN 'VIERNES' THEN 5 WHEN 'SABADO' THEN 6 END")
            ->get();

        $horas = $schedules->unique(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'))
            ->values()
            ->sortBy(fn ($s) => $s->hora_inicio)
            ->values();

        $tabla = [];
        foreach ($dias as $dia) {
            $tabla[$dia] = $schedules->where('dia', $dia)->groupBy(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'));
        }

        $bloquesDia = $this->bloquesDia();

        $courses = Course::all();

        // Materiales del nivel del curso (ej. 1ro Primaria no ve Informática, que es de Secundaria)
        $levelId = $course->grade?->level_id;
        $subjects = Subject::where('level_id', $levelId)->get();
        $teachers = Teacher::all();

        // Docente fijo por materia: la misma materia debe dictarla siempre el mismo
        // docente dentro del paralelo (la fuente de verdad es course_subjects; si la
        // materia ya figura en el horario, se respeta ese docente).
        $materiasDocentes = [];
        foreach ($schedules->groupBy('subject_id') as $subjId => $grupo) {
            $materiasDocentes[$subjId] = $grupo->first()->teacher_id;
        }
        foreach (CourseSubject::where('course_id', $course->id)->pluck('teacher_id', 'subject_id') as $subjId => $teacherId) {
            if (! isset($materiasDocentes[$subjId])) {
                $materiasDocentes[$subjId] = $teacherId;
            }
        }
        $docentesLabel = [];
        foreach ($teachers as $t) {
            $docentesLabel[$t->id] = trim($t->nombres.' '.$t->apellidos);
        }

        // Franjas de clase libres por día para "Agregar Clase"
        $franjasClase = collect($this->bloquesDia()->where('tipo', 'clase'))->map(fn ($b) => [$b['inicio'], $b['fin']]);

        $ocupados = Schedule::where('course_id', $course->id)
            ->get(['dia', 'hora_inicio', 'hora_fin'])
            ->map(fn ($s) => $s->dia.'|'.$s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'))
            ->toBase();

        $franjasLibresPorDia = [];
        $diasDisponibles = [];
        foreach ($dias as $dia) {
            $libres = [];
            foreach ($franjasClase as $franja) {
                [$ini, $fin] = $franja;
                if (! $ocupados->contains($dia.'|'.$ini.'-'.$fin)) {
                    $libres[] = ['inicio' => $ini, 'fin' => $fin];
                }
            }
            $franjasLibresPorDia[$dia] = $libres;
            if (count($libres) > 0) {
                $diasDisponibles[] = $dia;
            }
        }

        return view('schedules.show', compact('course', 'dias', 'horas', 'tabla', 'courses', 'subjects', 'teachers', 'schedules', 'bloquesDia', 'diasDisponibles', 'franjasLibresPorDia', 'materiasDocentes', 'docentesLabel'));
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
                ->where('estado', 'ACTIVO')
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

            $bloquesDia = $this->bloquesDia();

            return view('schedules.mi-horario-docente', compact('teacher', 'dias', 'horas', 'tabla', 'schedules', 'gestiones', 'bloquesDia'));
        }

        abort(403);
    }

    /**
     * Muestra el horario semanal de un docente concreto (vista admin).
     */
    public function horarioDocente(Teacher $teacher)
    {
        $schedules = $teacher->schedules()
            ->with(['course.grade', 'course.section', 'course.gestion', 'subject'])
            ->where('estado', 'ACTIVO')
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

        $bloquesDia = $this->bloquesDia();

        return view('schedules.docente', compact('teacher', 'dias', 'horas', 'tabla', 'schedules', 'gestiones', 'bloquesDia'));
    }

    private function renderHorarioCurso(string $vista, Course $course, array $extra = [])
    {
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('course_id', $course->id)
            ->where('estado', 'ACTIVO')
            ->orderBy('hora_inicio')
            ->orderByRaw("CASE dia WHEN 'LUNES' THEN 1 WHEN 'MARTES' THEN 2 WHEN 'MIERCOLES' THEN 3 WHEN 'JUEVES' THEN 4 WHEN 'VIERNES' THEN 5 WHEN 'SABADO' THEN 6 END")
            ->get();

        $horas = $schedules->unique(fn ($s) => $s->hora_inicio->format('H:i').'-'.$s->hora_fin->format('H:i'))
            ->values()
            ->sortBy(fn ($s) => $s->hora_inicio)
            ->values();

        $tabla = $this->tablaSemanal($schedules);

        $bloquesDia = $this->bloquesDia();

        $course->load(['grade.level', 'section', 'gestion']);

        return view($vista, array_merge($extra, compact('course', 'dias', 'horas', 'tabla', 'schedules', 'bloquesDia')));
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

    /**
     * Secuencia fija de bloques del día (incluye los recesos).
     * Los recesos se muestran de LUNES a VIERNES (y se omiten los sábados).
     */
    private function bloquesDia()
    {
        return collect([
            ['inicio' => '07:45', 'fin' => '08:30', 'tipo' => 'clase'],
            ['inicio' => '08:30', 'fin' => '09:15', 'tipo' => 'clase'],
            ['inicio' => '09:15', 'fin' => '09:30', 'tipo' => 'receso', 'nombre' => 'Receso 1'],
            ['inicio' => '09:30', 'fin' => '10:15', 'tipo' => 'clase'],
            ['inicio' => '10:15', 'fin' => '11:00', 'tipo' => 'clase'],
            ['inicio' => '11:00', 'fin' => '11:15', 'tipo' => 'receso', 'nombre' => 'Receso 2'],
            ['inicio' => '11:15', 'fin' => '12:00', 'tipo' => 'clase'],
        ]);
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

        // La misma materia en un paralelo la dicta siempre el mismo docente.
        // Se respeta el docente asignado en course_subjects (o el ya existente en el horario).
        $docenteReal = CourseSubject::where('course_id', $validated['course_id'])
            ->where('subject_id', $validated['subject_id'])
            ->value('teacher_id');
        if (! $docenteReal) {
            $docenteReal = Schedule::where('course_id', $validated['course_id'])
                ->where('subject_id', $validated['subject_id'])
                ->value('teacher_id');
        }
        if ($docenteReal) {
            $validated['teacher_id'] = $docenteReal;
        }

        $errorDocente = $this->validarCruceDocente($validated['teacher_id'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);
        if ($errorDocente) {
            return back()->withInput()->withErrors(['cruce' => $errorDocente]);
        }

        $errorAula = $this->validarCruceAula($validated['aula'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);
        if ($errorAula) {
            return back()->withInput()->withErrors(['cruce' => $errorAula]);
        }

        Schedule::create($validated);

        $schedulesUrl = null;
        if (isset($validated['course_id'])) {
            $schedulesUrl = route('schedules.show', $validated['course_id']);
        }

        if ($schedulesUrl) {
            return redirect($schedulesUrl)->with('mensaje', 'Clase agregada correctamente al horario.');
        }

        return redirect()->route('schedules.index')->with('mensaje', 'Horario creado correctamente.');
    }

    private function validarCruceDocente($docenteId, $dia, $horaInicio, $horaFin)
    {
        $cruce = Schedule::where('teacher_id', $docenteId)
            ->where('estado', 'ACTIVO')
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
            ->where('estado', 'ACTIVO')
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
        // Borrado lógico: se marca ELIMINADO para que el día/hora queden libres
        // para reasignar otra clase, sin perderse el registro.
        $schedule->update(['estado' => 'ELIMINADO']);

        return redirect()->route('schedules.show', $schedule->course_id)
            ->with('mensaje', 'Clase eliminada del horario. Puedes asignar otra en ese día y hora.');
    }
}
