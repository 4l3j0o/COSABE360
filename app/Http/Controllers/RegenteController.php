<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Licencia;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * HU-50 / HU-82: Rol Regente.
 *
 * Con HU-82 la asistencia solo se guarda por materia (la lista general del
 * Regente desaparece): su vista es un CONSOLIDADO de las listas por materia.
 * Además registra licencias (con evidencia y comunicado automático), que se
 * aplican a todas las materias del curso y quedan bloqueadas para el docente.
 */
class RegenteController extends Controller
{
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

        // Materias del curso (una sola vez por subject_id)
        $materias = SectionSubject::where('section_id', $section->id)
            ->with('subject')
            ->get()
            ->unique('subject_id')
            ->values();

        $studentIds = $students->pluck('id');

        // Asistencias del día por materia (subject_id NOT NULL)
        $asistencias = Attendance::where('section_id', $section->id)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereNotNull('subject_id')
            ->whereIn('student_id', $studentIds)
            ->get();

        $estadoPorEstudiante = $asistencias
            ->groupBy('student_id')
            ->map(fn ($regs) => $regs->keyBy('subject_id'));

        // Licencias activas que cubren la fecha
        $licenciasHoy = Licencia::where('section_id', $section->id)
            ->where('estado', 'ACTIVA')
            ->whereDate('fecha_inicio', '<=', $fecha->toDateString())
            ->whereDate('fecha_fin', '>=', $fecha->toDateString())
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        // Materia a corregir (selector)
        $subjectCorregir = $request->integer('subject_id') ?: $materias->first()?->subject_id;
        $corregirAsistencias = $subjectCorregir
            ? $asistencias->where('subject_id', $subjectCorregir)->keyBy('student_id')
            : collect();

        // Licencias registradas del curso (historial)
        $licencias = Licencia::where('section_id', $section->id)
            ->with('student')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        return view('regente.curso', compact(
            'section',
            'students',
            'fecha',
            'materias',
            'estadoPorEstudiante',
            'licenciasHoy',
            'subjectCorregir',
            'corregirAsistencias',
            'licencias',
        ));
    }

    /**
     * El Regente corrige la asistencia de UNA materia (por materia, nunca general).
     * Los estudiantes con licencia activa quedan bloqueados en LICENCIA.
     */
    public function guardarAsistencia(Request $request, Section $section): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'subject_id' => ['required', 'integer'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estado' => ['required', 'in:'.implode(',', Attendance::ESTADOS)],
            'asistencias.*.justificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $subjectId = (int) $validated['subject_id'];
        abort_unless(
            SectionSubject::where('section_id', $section->id)->where('subject_id', $subjectId)->exists(),
            422,
            'La materia no pertenece a este curso.'
        );

        $estudiantes = $section->students()->where('students.estado', 'ACTIVO')->pluck('id')->all();
        $fecha = Carbon::parse($validated['fecha'])->toDateString();
        $licenciados = $this->estudiantesLicenciados($section, $fecha);

        foreach ($validated['asistencias'] as $studentId => $data) {
            if (! in_array((int) $studentId, array_map('intval', $estudiantes), true)) {
                continue;
            }

            // El docente no puede modificar un día con licencia; el Regente
            // tampoco desde aquí (debe anular la licencia). Se mantiene LICENCIA.
            if (in_array((int) $studentId, $licenciados, true)) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'section_id' => $section->id,
                        'fecha' => $fecha,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'estado' => 'LICENCIA',
                        'registered_by_user_id' => auth()->id(),
                    ]
                );

                continue;
            }

            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'section_id' => $section->id,
                    'fecha' => $fecha,
                    'subject_id' => $subjectId,
                ],
                [
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]
            );
        }

        return redirect()->route('regente.curso', [
            'section' => $section,
            'fecha' => $fecha,
            'subject_id' => $subjectId,
        ])->with('success', 'Asistencia actualizada para el '.$fecha.'.');
    }

    /**
     * Registra una licencia: crea el expediente, aplica LICENCIA a todas las
     * materias en los días con clase y genera el comunicado con la evidencia.
     */
    public function registrarLicencia(Request $request, Section $section, Student $student): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'evidencia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        $inicio = Carbon::parse($validated['fecha_inicio'])->startOfDay();
        $fin = Carbon::parse($validated['fecha_fin'])->startOfDay();

        // Solo días con clase según el horario del curso (lun-sáb si no hay horario)
        $diasSemana = $this->diasConClase($section);
        $dias = [];
        for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
            if (in_array($d->dayOfWeekIso, $diasSemana, true)) {
                $dias[] = $d->toDateString();
            }
        }

        if (empty($dias)) {
            return back()->withInput()->with('error', 'El rango seleccionado no tiene días de clase para este curso.');
        }

        $subjectIds = SectionSubject::where('section_id', $section->id)
            ->pluck('subject_id')
            ->unique()
            ->values();

        $evidenciaPath = $request->hasFile('evidencia')
            ? $request->file('evidencia')->store('licencias', 'public')
            : null;

        // Comunicado automático a los docentes del curso (con la foto adjunta)
        $announcement = $this->crearComunicadoLicencia(
            $student,
            $section,
            $dias,
            $validated['motivo'] ?? null,
            $request->user(),
            $evidenciaPath,
        );

        Licencia::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'motivo' => $validated['motivo'] ?? null,
            'evidencia_path' => $evidenciaPath,
            'announcement_id' => $announcement->id,
            'estado' => 'ACTIVA',
            'created_by_user_id' => auth()->id(),
        ]);

        // Aplica LICENCIA a todas las materias en los días con clase
        foreach ($dias as $dia) {
            foreach ($subjectIds as $subjectId) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'fecha' => $dia,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'estado' => 'LICENCIA',
                        'justificacion' => $validated['motivo'] ?? null,
                        'registered_by_user_id' => auth()->id(),
                    ]
                );
            }
        }

        return redirect()->route('regente.curso', ['section' => $section])
            ->with('success', 'Licencia registrada para '.count($dias).' día(s) con clase y notificada a los docentes del curso.');
    }

    /**
     * Anula una licencia: revierte las filas LICENCIA del rango y archiva el comunicado.
     */
    public function anularLicencia(Request $request, Licencia $licencia): RedirectResponse
    {
        $this->autorizar($request);

        if ($licencia->estado !== 'ANULADA') {
            $licencia->update(['estado' => 'ANULADA']);

            Attendance::where('student_id', $licencia->student_id)
                ->where('section_id', $licencia->section_id)
                ->whereBetween('fecha', [
                    $licencia->fecha_inicio->toDateString(),
                    $licencia->fecha_fin->toDateString(),
                ])
                ->where('estado', 'LICENCIA')
                ->delete();

            if ($licencia->announcement) {
                $licencia->announcement->update(['estado' => 'ARCHIVADO']);
            }
        }

        return redirect()->route('regente.curso', ['section' => $licencia->section_id])
            ->with('success', 'Licencia anulada correctamente.');
    }

    /** Días ISO (1=lunes .. 6=sábado) en que el curso tiene clase. */
    private function diasConClase(Section $section): array
    {
        $map = ['LUNES' => 1, 'MARTES' => 2, 'MIERCOLES' => 3, 'JUEVES' => 4, 'VIERNES' => 5, 'SABADO' => 6];

        $dias = $section->schedules()
            ->where('estado', 'ACTIVO')
            ->whereNotNull('dia')
            ->pluck('dia')
            ->map(fn ($d) => $map[strtoupper((string) $d)] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $dias !== [] ? $dias : [1, 2, 3, 4, 5, 6];
    }

    /**
     * Crea un comunicado PUBLICADO dirigido a los docentes que dictan en el
     * curso del estudiante, con la evidencia adjunta.
     */
    private function crearComunicadoLicencia(Student $student, Section $section, array $dias, ?string $motivo, $autor, ?string $evidenciaPath = null): Announcement
    {
        $announcement = Announcement::create([
            'titulo' => 'Licencia de estudiante: '.$student->apellidos.' '.$student->nombres,
            'contenido' => "Se informa que el(la) estudiante **{$student->nombres} {$student->apellidos}** del paralelo "
                .($section->codigo ?: $section->nombre).' se encuentra con LICENCIA los siguientes día(s): '.implode(', ', $dias)
                .($motivo ? '. Motivo: '.$motivo : '.'),
            'author_id' => $autor->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
            'imagen_path' => $evidenciaPath,
        ]);

        $docenteUserIds = SectionSubject::where('section_id', $section->id)
            ->with('teacher')
            ->get()
            ->pluck('teacher.user_id')
            ->filter()
            ->unique()
            ->values();

        if ($docenteUserIds->isEmpty()) {
            $announcement->recipients()->create(['tipo_destinatario' => 'DOCENTES']);

            return $announcement;
        }

        foreach ($docenteUserIds as $userId) {
            $announcement->recipients()->create([
                'tipo_destinatario' => 'CURSO_ESPECIFICO',
                'section_id' => $section->id,
                'user_id' => $userId,
            ]);
        }

        return $announcement;
    }

    /** Ids de estudiantes del curso con licencia activa que cubre la fecha. */
    private function estudiantesLicenciados(Section $section, string $fecha): array
    {
        return Licencia::where('section_id', $section->id)
            ->where('estado', 'ACTIVA')
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_fin', '>=', $fecha)
            ->pluck('student_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function autorizar(Request $request): void
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['Regente', 'Administrador', 'Director', 'Secretaría']), 403);
    }
}
