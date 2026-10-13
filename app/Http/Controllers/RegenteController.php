<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-50: Rol Regente.
 * Gestiona listas de asistencia de todos los cursos (puede modificar a su criterio),
 * registra permisos/licencias y, al registrar una licencia, genera automáticamente un
 * comunicado dirigido únicamente a los docentes que dictan en el curso del estudiante.
 */
class RegenteController extends Controller
{
    public function __construct()
    {
        // Solo Regente (y roles superiores) acceden a este módulo.
        $this->middleware('auth');
    }

    public function cursos(Request $request): View
    {
        $this->autorizar($request);

        $cursos = Section::with(['grade', 'gestion'])
            ->ordenadoPorNivel()
            ->get();

        return view('regente.cursos', compact('cursos'));
    }

    public function verCurso(Request $request, Section $section): View
    {
        $this->autorizar($request);

        $section->load(['grade', 'gestion']);

        $students = $section->students()
            ->where('students.estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $fecha = $request->query('fecha') ? Carbon::parse($request->query('fecha')) : now();

        $asistencias = Attendance::where('section_id', $section->id)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $materias = SectionSubject::where('section_id', $section->id)->with('subject')->get();

        return view('regente.curso', compact('section', 'students', 'fecha', 'asistencias', 'materias'));
    }

    /**
     * El Regente modifica la lista de asistencia a su antojo (todos los estados, cualquier fecha).
     */
    public function guardarAsistencia(Request $request, Section $section): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estado' => ['required', 'in:'.implode(',', Attendance::ESTADOS)],
            'asistencias.*.justificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $estudiantesCurso = $section->students()->where('students.estado', 'ACTIVO')->pluck('id')->toArray();
        $fecha = Carbon::parse($validated['fecha'])->toDateString();

        $seDetectoLicencia = false;

        foreach ($validated['asistencias'] as $studentId => $data) {
            if (! in_array((int) $studentId, $estudiantesCurso, true)) {
                continue;
            }

            $existente = Attendance::where('student_id', $studentId)
                ->where('section_id', $section->id)
                ->whereDate('fecha', $fecha)
                ->whereNull('subject_id')
                ->first();

            if ($existente) {
                $existente->update([
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            } else {
                Attendance::create([
                    'student_id' => $studentId,
                    'section_id' => $section->id,
                    'subject_id' => null,
                    'fecha' => $fecha,
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            }

            if ($data['estado'] === 'LICENCIA') {
                $seDetectoLicencia = true;
            }
        }

        if ($seDetectoLicencia) {
            // Se notificará a los docentes del curso sobre las licencias registradas.
            $this->notificarLicencias($section, $validated['asistencias'], $fecha, $request->user());
        }

        return redirect()->route('regente.curso', ['section' => $section, 'fecha' => $fecha])
            ->with('success', 'Asistencia actualizada por el Regente para el '.$fecha.'.');
    }

    /**
     * Registra una licencia puntual para un estudiante en uno o más días y notifica a los docentes.
     */
    public function registrarLicencia(Request $request, Section $section, Student $student): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        $inicio = Carbon::parse($validated['fecha_inicio']);
        $fin = Carbon::parse($validated['fecha_fin']);

        $dias = [];
        for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
            $dias[] = $d->toDateString();
            Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'section_id' => $section->id,
                    'fecha' => $d->toDateString(),
                    'subject_id' => null,
                ],
                [
                    'estado' => 'LICENCIA',
                    'justificacion' => $validated['motivo'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]
            );
        }

        // Comunicado automático a los docentes del curso del estudiante
        $this->crearComunicadoLicencia($student, $section, $dias, $validated['motivo'] ?? null, $request->user());

        return redirect()->route('regente.curso', ['section' => $section])
            ->with('success', 'Licencia registrada para '.count($dias).' día(s) y notificado a los docentes del curso.');
    }

    /**
     * Crea un comunicado PUBLICADO dirigido a los docentes que dictan en el curso del estudiante.
     */
    private function crearComunicadoLicencia(Student $student, Section $section, array $dias, ?string $motivo, $autor): void
    {
        $announcement = Announcement::create([
            'titulo' => 'Licencia de estudiante: '.$student->apellidos.' '.$student->nombres,
            'contenido' => "Se informa que el(la) estudiante **{$student->nombres} {$student->apellidos}** de {$section->nombre} "
                .'se encuentra con LICENCIA los siguientes día(s): '.implode(', ', $dias)
                .($motivo ? '. Motivo: '.$motivo : '.'),
            'author_id' => $autor->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
        ]);

        // Docentes que dictan en este curso
        $docenteUserIds = SectionSubject::where('section_id', $section->id)
            ->with('teacher.user')
            ->get()
            ->pluck('teacher.user_id')
            ->filter()
            ->unique()
            ->values();

        if ($docenteUserIds->isEmpty()) {
            // Si no hay docentes vinculados, se dirige al tipo DOCENTES (general).
            $announcement->recipients()->create(['tipo_destinatario' => 'DOCENTES']);

            return;
        }

        foreach ($docenteUserIds as $userId) {
            $announcement->recipients()->create([
                'tipo_destinatario' => 'CURSO_ESPECIFICO',
                'section_id' => $section->id,
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Al detectar LICENCIA en el guardado masivo, notifica a los docentes del curso.
     */
    private function notificarLicencias(Section $section, array $asistencias, string $fecha, $autor): void
    {
        $licenciados = [];
        foreach ($asistencias as $studentId => $data) {
            if ($data['estado'] === 'LICENCIA') {
                $licenciados[] = (int) $studentId;
            }
        }

        if (empty($licenciados)) {
            return;
        }

        $students = Student::whereIn('id', $licenciados)->get();

        foreach ($students as $student) {
            $this->crearComunicadoLicencia($student, $section, [$fecha], null, $autor);
        }
    }

    private function autorizar(Request $request): void
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['Regente', 'Administrador', 'Director', 'Secretaría']), 403);
    }
}
