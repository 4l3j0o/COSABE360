<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Level;
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
        $this->autorizarPermiso($request, 'licencias.ver');

        $levels = Level::orderBy('orden')->get();

        $levelId = $request->integer('level_id') ?: null;
        $gradeId = $request->integer('grade_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        // Grados del nivel elegido (cascada Nivel -> Grado).
        $grades = Grade::query()
            ->when($levelId, fn ($q) => $q->where('level_id', $levelId))
            ->orderBy('orden')
            ->get();

        if ($gradeId && ! $grades->contains('id', $gradeId)) {
            $gradeId = null;
        }

        // Paralelos del grado elegido (cascada Grado -> Paralelo).
        $sections = Section::with(['grade.level', 'gestion'])
            ->when($levelId, fn ($q) => $q->whereHas('grade', fn ($g) => $g->where('level_id', $levelId)))
            ->when($gradeId, fn ($q) => $q->where('grade_id', $gradeId))
            ->ordenadoPorNivel()
            ->get();

        if ($sectionId && ! $sections->contains('id', $sectionId)) {
            $sectionId = null;
        }

        $section = $sectionId ? $sections->firstWhere('id', $sectionId) : null;

        $students = $section
            ? $section->students()
                ->where('students.estado', 'ACTIVO')
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get()
            : collect();

        $licencias = Licencia::with(['student', 'section.grade.level', 'section.gestion'])
            ->when($section, fn ($q) => $q->where('section_id', $section->id))
            ->when(! $section && $levelId, fn ($q) => $q->whereHas('section.grade', fn ($g) => $g->where('level_id', $levelId)))
            ->when(! $section && $gradeId, fn ($q) => $q->whereHas('section', fn ($s) => $s->where('grade_id', $gradeId)))
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        return view('regente.licencias', compact(
            'levels',
            'grades',
            'sections',
            'levelId',
            'gradeId',
            'section',
            'students',
            'licencias',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autorizarPermiso($request, 'licencias.crear');

        $validated = $request->validate([
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'evidencia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ], [
            'evidencia.image' => 'La evidencia debe ser una imagen (JPG, PNG, GIF o WEBP).',
            'evidencia.mimes' => 'La evidencia debe ser una imagen JPG, PNG, GIF o WEBP.',
            'evidencia.max' => 'La evidencia no debe superar los 10 MB.',
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
        $this->autorizarPermiso($request, 'licencias.autorizar');

        $this->licencias->anular($licencia);

        return redirect()->route('regente.licencias', ['section_id' => $licencia->section_id])
            ->with('success', 'Licencia anulada correctamente.');
    }

    private function autorizarPermiso(Request $request, string $permiso): void
    {
        abort_unless($request->user()?->puede($permiso), 403);
    }
}
