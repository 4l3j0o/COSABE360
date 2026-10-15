<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $levels  = Level::where('estado', '!=', 'ELIMINADO')->orderBy('orden')->get();
        $grados  = Grade::with('level')->where('estado', '!=', 'ELIMINADO')->orderBy('level_id')->orderBy('orden')->get();
        $docentes = Teacher::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
        $gestiones = \App\Models\AcademicYear::orderByDesc('nombre')->get();

        $sections = Section::with(['grade.level', 'responsable'])
            ->where('estado', '!=', 'ELIMINADO')
            ->when($request->query('level_id'), function ($q, $v) {
                $q->whereHas('grade', fn ($g) => $g->where('level_id', $v));
            })
            ->when($request->query('grade_id'), fn ($q, $v) => $q->where('grade_id', $v))
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->ordenadoPorNivel()
            ->paginate(10)
            ->withQueryString();

        $gradoActual = $request->query('grade_id')
            ? Grade::with('level')->find($request->query('grade_id'))
            : null;

        return view('sections.index', compact('sections', 'levels', 'grados', 'docentes', 'gestiones', 'gradoActual'));
    }

    private function apiIndex(Request $request): AnonymousResourceCollection
    {
        $sections = Section::with(['grade', 'responsable']);

        if ($request->filled('level_id')) {
            $sections->whereHas('grade', fn ($q) => $q->where('level_id', $request->input('level_id')));
        }

        if ($request->filled('grade_id')) {
            $sections->where('grade_id', $request->grade_id);
        }

        $sections->ordenadoPorNivel();

        return SectionResource::collection($sections->get());
    }

    public function store(StoreSectionRequest $request)
    {
        $validated = $request->validated();
        $section = Section::create($validated + ['estado' => 'ACTIVO']);

        if ($request->expectsJson()) {
            return (new SectionResource($section->fresh()->load(['grade', 'responsable'])))->response()->setStatusCode(201);
        }

        return $this->deVuelta('sections.index', ['level_id', 'grade_id', 'estado', 'page'], 'Paralelo creado correctamente.');
    }

    public function show(Section $section)
    {
        if (request()->expectsJson()) {
            return new SectionResource($section->load(['grade', 'responsable']));
        }

        return redirect()->route('sections.index');
    }

    public function update(UpdateSectionRequest $request, Section $section)
    {
        $section->update($request->validated());

        if ($request->expectsJson()) {
            return new SectionResource($section->fresh()->load(['grade', 'responsable']));
        }

        return $this->deVuelta('sections.index', ['level_id', 'grade_id', 'estado', 'page'], 'Paralelo actualizado correctamente.');
    }

    public function toggle(Section $section)
    {
        $nuevo = $section->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $section->update(['estado' => $nuevo]);

        $accion = $nuevo === 'ACTIVO' ? 'activado' : 'desactivado';

        return $this->deVuelta('sections.index', ['level_id', 'grade_id', 'estado', 'page'], "Paralelo {$accion} correctamente.");
    }

    public function destroy(Section $section)
    {
        $tieneMatriculados = Schema::hasTable('registrations')
            && DB::table('registrations')
                ->where('section_id', $section->id)
                ->where('estado', '!=', 'CANCELADA')
                ->exists();

        if ($tieneMatriculados) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el paralelo porque tiene estudiantes matriculados.',
                ], 409);
            }

            return $this->deVuelta('sections.index', ['level_id', 'grade_id', 'estado', 'page'], 'No se puede eliminar el paralelo porque tiene estudiantes matriculados.', 'error');
        }

        $section->update(['estado' => 'ELIMINADO']);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Paralelo eliminado correctamente.']);
        }

        return $this->deVuelta('sections.index', ['level_id', 'grade_id', 'estado', 'page'], 'Paralelo eliminado correctamente.');
    }
}
