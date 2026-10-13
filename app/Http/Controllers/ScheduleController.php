<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $paralelos = Section::withCount(['schedules' => function ($query) {
            $query->where('estado', 'ACTIVO')->whereHas('subject')->whereHas('teacher');
        }])
            ->ordenadoPorNivel()
            ->paginate(10)
            ->withQueryString();

        $cursosSinHorario = Section::withCount(['schedules' => function ($query) {
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
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        return redirect()
            ->route('schedules.show', $validated['section_id'])
            ->with('mensaje', 'Horario iniciado. Ahora asigna las clases del paralelo.');
    }

    public function show(Section $section)
    {
        $section->load(['grade.level', 'gestion']);

        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('section_id', $section->id)
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

        $sections = Section::all();

        // Materiales del nivel del curso (ej. 1ro Primaria no ve Informática, que es de Secundaria)
        $levelId = $section->grade?->level_id;
        $subjects = Subject::where('level_id', $levelId)->get();
        $teachers = Teacher::all();

        // Docente fijo por materia: la misma materia debe dictarla siempre el mismo
        // docente dentro del paralelo (la fuente de verdad es section_subjects; si la
        // materia ya figura en el horario, se respeta ese docente).
        $materiasDocentes = [];
        foreach ($schedules->groupBy('subject_id') as $subjId => $grupo) {
            $materiasDocentes[$subjId] = $grupo->first()->teacher_id;
        }
        foreach (SectionSubject::where('section_id', $section->id)->pluck('teacher_id', 'subject_id') as $subjId => $teacherId) {
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

        $ocupados = Schedule::where('section_id', $section->id)
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

        return view('schedules.show', compact('section', 'dias', 'horas', 'tabla', 'sections', 'subjects', 'teachers', 'schedules', 'bloquesDia', 'diasDisponibles', 'franjasLibresPorDia', 'materiasDocentes', 'docentesLabel'));
    }

    /**
     * Lista los estudiantes matriculados (Registration ACTIVA) de un paralelo.
     */
    public function estudiantes(Section $section)
    {
        $section->load(['grade', 'gestion']);

        $estudiantes = $section->students()
            ->with(['user'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('schedules.estudiantes', compact('section', 'estudiantes'));
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

            $section = $student->activeSection();

            if (! $section) {
                $section = null;
                return view('schedules.mi-horario-estudiante', [
                    'sinCurso' => true,
                    'section' => null,
                    'dias' => [],
                    'horas' => collect(),
                    'tabla' => [],
                ]);
            }

            return $this->renderHorarioCurso(
                'schedules.mi-horario-estudiante',
                $section,
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
                ->with(['section.grade', 'section', 'section.gestion', 'subject'])
                ->where('estado', 'ACTIVO')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

            $grupos = $this->tablasPorGrupo($schedules);

            $gestiones = $schedules->map(fn ($s) => $s->section?->gestion?->nombre)
                ->filter()
                ->unique()
                ->values();

            $bloquesDia = $this->bloquesDia();
            $visitas = $this->visitasDe($teacher);

            return view('schedules.mi-horario-docente', compact('teacher', 'dias', 'grupos', 'schedules', 'gestiones', 'bloquesDia', 'visitas'));
        }

        abort(403);
    }

    /**
     * Muestra el horario semanal de un docente concreto (vista admin).
     */
    public function horarioDocente(Teacher $teacher)
    {
        $schedules = $teacher->schedules()
            ->with(['section.grade', 'section', 'section.gestion', 'subject'])
            ->where('estado', 'ACTIVO')
            ->orderBy('hora_inicio')
            ->get();

        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $grupos = $this->tablasPorGrupo($schedules);

        $gestiones = $schedules->map(fn ($s) => $s->section?->gestion?->nombre)
            ->filter()
            ->unique()
            ->values();

        $bloquesDia = $this->bloquesDia();
        $visitas = $this->visitasDe($teacher);

        return view('schedules.docente', compact('teacher', 'dias', 'grupos', 'schedules', 'gestiones', 'bloquesDia', 'visitas'));
    }

    /**
     * HU-70: horario de visita para padres. El docente gestiona el suyo;
     * admin/dirección gestionan cualquiera (teacher_id). Los estudiantes y el
     * personal lo consultan en visitasIndex.
     */
    public function misVisitas(Request $request)
    {
        $user = $request->user();
        $teachers = Teacher::where('estado', 'ACTIVO')->orderBy('apellidos')->get();

        if ($user->hasRole('Docente')) {
            $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
            abort_unless($teacher, 403, 'No se encontró un perfil de docente para esta cuenta.');
        } else {
            $teacher = $request->query('teacher_id')
                ? Teacher::findOrFail($request->query('teacher_id'))
                : $teachers->first();
            abort_unless($teacher, 404, 'Sin docentes registrados.');
        }

        $visitas = $this->visitasDe($teacher);

        return view('visitas.mis', compact('teacher', 'teachers', 'visitas'));
    }

    public function storeVisita(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'dia' => ['required', 'in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'aula' => ['nullable', 'string', 'max:50'],
        ]);

        if ($user->hasRole('Docente')) {
            $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
            abort_unless($teacher, 403, 'No se encontró un perfil de docente para esta cuenta.');
            $validated['teacher_id'] = $teacher->id;
        } else {
            $request->validate(['teacher_id' => ['required', 'exists:teachers,id']]);
        }

        $error = $this->validarCruceDocente($validated['teacher_id'], $validated['dia'], $validated['hora_inicio'], $validated['hora_fin']);
        if ($error) {
            return back()->withInput()->withErrors(['cruce' => $error]);
        }

        Schedule::create($validated + ['tipo' => 'VISITA', 'estado' => 'ACTIVO']);

        return redirect()->route('visitas.mis', ['teacher_id' => $validated['teacher_id']])
            ->with('mensaje', 'Horario de visita registrado.');
    }

    public function destroyVisita(Request $request, Schedule $schedule)
    {
        abort_unless($schedule->tipo === 'VISITA', 404, 'Solo se gestionan horarios de visita.');

        $user = $request->user();
        if ($user->hasRole('Docente')) {
            $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
            abort_unless($teacher && $schedule->teacher_id === $teacher->id, 403, 'Solo puedes gestionar tu propio horario de visita.');
        }

        $teacherId = $schedule->teacher_id;
        $schedule->update(['estado' => 'ELIMINADO']);

        return redirect()->route('visitas.mis', ['teacher_id' => $teacherId])
            ->with('mensaje', 'Horario de visita eliminado.');
    }

    /**
     * HU-70: consulta pública de horarios de visita (estudiantes y personal).
     */
    public function visitasIndex(Request $request)
    {
        $teachers = Teacher::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
        $teacherId = $request->query('teacher_id');

        $visitas = Schedule::with(['teacher'])
            ->where('tipo', 'VISITA')
            ->where('estado', 'ACTIVO')
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->orderBy('teacher_id')
            ->orderByRaw("CASE dia WHEN 'LUNES' THEN 1 WHEN 'MARTES' THEN 2 WHEN 'MIERCOLES' THEN 3 WHEN 'JUEVES' THEN 4 WHEN 'VIERNES' THEN 5 WHEN 'SABADO' THEN 6 END")
            ->orderBy('hora_inicio')
            ->paginate(20)
            ->withQueryString();

        return view('visitas.index', compact('teachers', 'visitas', 'teacherId'));
    }

    private function visitasDe(Teacher $teacher)
    {
        return $teacher->schedules()
            ->where('tipo', 'VISITA')
            ->where('estado', 'ACTIVO')
            ->orderByRaw("CASE dia WHEN 'LUNES' THEN 1 WHEN 'MARTES' THEN 2 WHEN 'MIERCOLES' THEN 3 WHEN 'JUEVES' THEN 4 WHEN 'VIERNES' THEN 5 WHEN 'SABADO' THEN 6 END")
            ->orderBy('hora_inicio')
            ->get();
    }

    private function renderHorarioCurso(string $vista, Section $section, array $extra = [])
    {
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('section_id', $section->id)
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

        $section->load(['grade.level', 'gestion']);

        return view($vista, array_merge($extra, compact('section', 'dias', 'horas', 'tabla', 'schedules', 'bloquesDia')));
    }

    /**
     * Tablas de CLASES por nivel (Inicial, Primaria, Secundaria por separado).
     * Cada tabla trae la grilla COMPLETA de periodos de los niveles donde el
     * docente tiene bloques; solo se marcan sus bloques.
     */
    private function tablasPorGrupo($schedules): array
    {
        $defs = [
            'INICIAL' => ['titulo' => 'Nivel Inicial', 'accent' => 'inicial'],
            'PRIMARIA' => ['titulo' => 'Nivel Primaria', 'accent' => 'primaria'],
            'SECUNDARIA' => ['titulo' => 'Nivel Secundaria', 'accent' => 'secundaria'],
        ];

        $clases = $schedules->where('tipo', 'CLASE')->values();

        // Dos pasadas: primero niveles firmes, luego los ambiguos se resuelven
        // prefiriendo un nivel que el docente ya ocupa.
        $asignado = [];
        foreach ($clases as $s) {
            $asignado[$s->id] = $this->nivelDeBloque($s);
        }
        $nivelesDocente = array_values(array_unique(array_filter($asignado)));
        foreach ($clases as $s) {
            if (! $asignado[$s->id]) {
                $asignado[$s->id] = $this->nivelDeBloque($s, $nivelesDocente);
            }
        }

        $grupos = [];

        foreach ($defs as $nivel => $def) {
            $enNivel = $clases->filter(fn ($s) => ($asignado[$s->id] ?? null) === $nivel)->values();
            if ($enNivel->isEmpty()) {
                continue;
            }

            $filas = collect();
            foreach (Schedule::PERIODOS_NIVEL[$nivel] as [$ini, $fin]) {
                $filas->push(['inicio' => $ini, 'fin' => $fin]);
            }
            // Franjas propias fuera del estándar (ej. salida anticipada) también se muestran
            foreach ($enNivel as $s) {
                $clave = $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i');
                if (! $filas->contains(fn ($f) => $f['inicio'] . '-' . $f['fin'] === $clave)) {
                    $filas->push(['inicio' => $s->hora_inicio->format('H:i'), 'fin' => $s->hora_fin->format('H:i')]);
                }
            }
            $filas = $filas->sortBy('inicio')->values();

            // Recesos = huecos entre periodos consecutivos del nivel
            $recesos = collect();
            foreach (Schedule::PERIODOS_NIVEL[$nivel] as $i => [$pi, $pf]) {
                $sig = Schedule::PERIODOS_NIVEL[$nivel][$i + 1] ?? null;
                if ($sig && $pf < $sig[0]) {
                    $recesos->push(['inicio' => $pf, 'fin' => $sig[0]]);
                }
            }
            $recesos = $recesos->unique(fn ($r) => $r['inicio'] . '-' . $r['fin'])->sortBy('inicio')->values();

            $tabla = [];
            foreach (['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'] as $dia) {
                $tabla[$dia] = $enNivel->where('dia', $dia)
                    ->groupBy(fn ($s) => $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i'));
            }

            $grupos[] = ['titulo' => $def['titulo'], 'accent' => $def['accent'], 'filas' => $filas, 'recesos' => $recesos, 'tabla' => $tabla];
        }

        return $grupos;
    }

    private function nivelDeBloque($s, array $nivelesDocente = []): ?string
    {
        $nivelNombre = $s->section?->grade?->level?->nombre ?? '';
        foreach (['INICIAL' => 'inicial', 'PRIMARIA' => 'primaria', 'SECUNDARIA' => 'secundaria'] as $nivel => $aguja) {
            if (str_contains(mb_strtolower($nivelNombre), $aguja)) {
                return $nivel;
            }
        }

        $ini = $s->hora_inicio->format('H:i');
        $fin = $s->hora_fin->format('H:i');

        $nivel = Schedule::nivelDeFranja($ini, $fin);
        if ($nivel) {
            return $nivel;
        }

        // Franja ambigua (ej. salida anticipada dentro de dos niveles):
        // con contexto se queda con un nivel que el docente ya ocupa;
        // sin contexto se deja null para resolver en segunda pasada.
        foreach (Schedule::nivelesDeFranja($ini, $fin) as $candidato) {
            if (in_array($candidato, $nivelesDocente, true)) {
                return $candidato;
            }
        }

        $cruzan = Schedule::nivelesDeFranja($ini, $fin);
        if ($cruzan && ! empty($nivelesDocente)) {
            return $cruzan[0];
        }
        if ($cruzan) {
            return null;
        }

        return $fin <= '12:30' ? 'PRIMARIA' : 'SECUNDARIA';
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
        $sections = Section::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        return view('schedules.create', compact('sections', 'subjects', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'dia' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:50',
        ]);

        // La misma materia en un paralelo la dicta siempre el mismo docente.
        // Se respeta el docente asignado en section_subjects (o el ya existente en el horario).
        $docenteReal = SectionSubject::where('section_id', $validated['section_id'])
            ->where('subject_id', $validated['subject_id'])
            ->value('teacher_id');
        if (! $docenteReal) {
            $docenteReal = Schedule::where('section_id', $validated['section_id'])
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
        if (isset($validated['section_id'])) {
            $schedulesUrl = route('schedules.show', $validated['section_id']);
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

        if (! $schedule->section_id) {
            return redirect()->route('schedules.index')
                ->with('mensaje', 'Bloque de ocupación eliminado del horario.');
        }

        return redirect()->route('schedules.show', $schedule->section_id)
            ->with('mensaje', 'Clase eliminada del horario. Puedes asignar otra en ese día y hora.');
    }
}
