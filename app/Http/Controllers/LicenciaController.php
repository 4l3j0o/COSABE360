<?php

namespace App\Http\Controllers;

use App\Models\Licencia;
use App\Models\Section;
use App\Models\Student;
use App\Services\LicenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HU-82: módulo de licencias, independiente de "Listas de Asistencia".
 *
 * El Regente registra aquí la licencia de un estudiante (expediente, evidencia
 * y comunicado automático a los docentes) y puede anularla. La asistencia
 * propiamente dicha se gestiona en el módulo de listas.
 */
class LicenciaController extends Controller
{
    public function __construct(private LicenciaService $licencias) {}

    public function index(Request $request): View
    {
        $this->autorizar($request);

        $sections = Section::with(['grade', 'gestion'])
            ->ordenadoPorNivel()
            ->get();

        $sectionId = $request->integer('section_id') ?: null;
        $section = $sectionId ? $sections->firstWhere('id', $sectionId) : null;

        $students = $section
            ? $section->students()
                ->where('students.estado', 'ACTIVO')
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get()
            : collect();

        $licencias = Licencia::with(['student', 'section.grade', 'section.gestion'])
            ->when($section, fn ($q) => $q->where('section_id', $section->id))
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        return view('regente.licencias', compact('sections', 'section', 'students', 'licencias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'evidencia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        $section = Section::findOrFail($validated['section_id']);
        $student = Student::findOrFail($validated['student_id']);

        $pertenece = $section->students()
            ->where('students.id', $student->id)
            ->where('students.estado', 'ACTIVO')
            ->exists();
        abort_unless($pertenece, 422, 'El estudiante no pertenece a este curso.');

        $dias = $this->licencias->diasDelRango($section, $validated['fecha_inicio'], $validated['fecha_fin']);

        if (empty($dias)) {
            return back()->withInput()->with('error', 'El rango seleccionado no tiene días de clase para este curso.');
        }

        $this->licencias->registrar(
            $section,
            $student,
            $dias,
            $validated['fecha_inicio'],
            $validated['fecha_fin'],
            $validated['motivo'] ?? null,
            $request->file('evidencia'),
            $request->user(),
        );

        return redirect()->route('regente.licencias', ['section_id' => $section->id])
            ->with('success', 'Licencia registrada para '.count($dias).' día(s) con clase y notificada a los docentes del curso.');
    }

    public function anular(Request $request, Licencia $licencia): RedirectResponse
    {
        $this->autorizar($request);

        $this->licencias->anular($licencia);

        return redirect()->route('regente.licencias', ['section_id' => $licencia->section_id])
            ->with('success', 'Licencia anulada correctamente.');
    }

    private function autorizar(Request $request): void
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['Regente', 'Administrador', 'Director', 'Secretaría']), 403);
    }
}
