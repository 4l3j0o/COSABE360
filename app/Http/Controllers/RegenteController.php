<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Licencia;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Services\LicenciaService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-50 / HU-82: Rol Regente.
 *
 * Con HU-82 la asistencia solo se guarda por materia (la lista general del
 * Regente desaparece): su vista es un CONSOLIDADO de las listas por materia.
 * Las licencias se gestionan en su propio módulo (ver LicenciaController).
 */
class RegenteController extends Controller
{
    public function __construct(private LicenciaService $licencias) {}

    public function cursos(Request $request): View
    {
        $this->autorizar($request);

        $cursos = Section::with(['grade.level', 'gestion'])
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

        // Lista a mostrar: general (todas las materias) por defecto, o una materia.
        $subjectIds = $materias->pluck('subject_id');
        $subjectSel = $request->query('subject_id');
        if ($subjectSel !== null && $subjectSel !== 'general' && $subjectIds->contains((int) $subjectSel)) {
            $subjectCorregir = (int) $subjectSel;
        } else {
            $subjectCorregir = 'general';
        }

        // En modo general la grilla se precarga con la primera materia del día.
        $subjectCargar = $subjectCorregir === 'general' ? $materias->first()?->subject_id : $subjectCorregir;
        $corregirAsistencias = $subjectCargar
            ? $asistencias->where('subject_id', $subjectCargar)->keyBy('student_id')
            : collect();

        return view('regente.curso', compact(
            'section',
            'students',
            'fecha',
            'materias',
            'estadoPorEstudiante',
            'licenciasHoy',
            'subjectCorregir',
            'corregirAsistencias',
        ));
    }

    /**
     * El Regente llama la lista. En modo "general" rellena TODAS las materias
     * del curso ese día (sin pisar las ya registradas por un docente); en modo
     * materia corrige una sola. Los estudiantes con licencia quedan en LICENCIA.
     */
    public function guardarAsistencia(Request $request, Section $section): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'subject_id' => ['required'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estado' => ['required', 'in:'.implode(',', Attendance::ESTADOS)],
            'asistencias.*.justificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $subjectIds = SectionSubject::where('section_id', $section->id)
            ->pluck('subject_id')
            ->unique()
            ->values();

        $esGeneral = $validated['subject_id'] === 'general';

        if ($esGeneral) {
            $materias = $subjectIds;
        } else {
            $subjectId = (int) $validated['subject_id'];
            abort_unless($subjectIds->contains($subjectId), 422, 'La materia no pertenece a este curso.');
            $materias = collect([$subjectId]);
        }

        $estudiantes = $section->students()->where('students.estado', 'ACTIVO')->pluck('id')->all();
        $fecha = Carbon::parse($validated['fecha'])->toDateString();
        $licenciados = $this->licencias->estudiantesLicenciados($section, $fecha);

        foreach ($validated['asistencias'] as $studentId => $data) {
            if (! in_array((int) $studentId, array_map('intval', $estudiantes), true)) {
                continue;
            }

            $esLicenciado = in_array((int) $studentId, $licenciados, true);

            foreach ($materias as $subjectId) {
                $llave = [
                    'student_id' => $studentId,
                    'section_id' => $section->id,
                    'fecha' => $fecha,
                    'subject_id' => $subjectId,
                ];

                // El docente no puede modificar un día con licencia: se mantiene LICENCIA.
                if ($esLicenciado) {
                    Attendance::updateOrCreate($llave, [
                        'estado' => 'LICENCIA',
                        'registered_by_user_id' => auth()->id(),
                    ]);

                    continue;
                }

                // La lista general solo RELLENA: no pisa lo que un docente ya
                // llamó ese día. La corrección por materia sí sobreescribe.
                if ($esGeneral) {
                    Attendance::firstOrCreate($llave, [
                        'estado' => $data['estado'],
                        'justificacion' => $data['justificacion'] ?? null,
                        'registered_by_user_id' => auth()->id(),
                    ]);

                    continue;
                }

                Attendance::updateOrCreate($llave, [
                    'estado' => $data['estado'],
                    'justificacion' => $data['justificacion'] ?? null,
                    'registered_by_user_id' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('regente.curso', [
            'section' => $section,
            'fecha' => $fecha,
            'subject_id' => $esGeneral ? 'general' : $validated['subject_id'],
        ])->with('success', $esGeneral
            ? 'Lista general registrada para el '.$fecha.'. Se rellenaron solo las materias sin registro.'
            : 'Asistencia actualizada para el '.$fecha.'.');
    }

    private function autorizar(Request $request): void
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['Regente', 'Administrador', 'Director', 'Secretaría']), 403);
    }
}
