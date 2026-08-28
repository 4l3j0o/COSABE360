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

        $levels  = Level::orderBy('orden')->get();
        $grados  = Grade::with('level')->orderBy('level_id')->orderBy('orden')->get();
        $docentes = Teacher::where('estado', 'ACTIVO')->orderBy('apellidos')->get();

        $sections = Section::with(['grade.level', 'responsable'])
            ->when($request->query('level_id'), function ($q, $v) {
                $q->whereHas('grade', fn ($g) => $g->where('level_id', $v));
            })
            ->when($request->query('grado_id'), fn ($q, $v) => $q->where('grado_id', $v))
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('sections.index', compact('sections', 'levels', 'grados', 'docentes'));
    }

    private function apiIndex(Request $request): AnonymousResourceCollection
    {
        $sections = Section::with(['grade', 'responsable']);

        if ($request->filled('level_id')) {
            $sections->whereHas('grade', fn ($q) => $q->where('level_id', $request->input('level_id')));
        }

        if ($request->filled('grado_id')) {
            $sections->where('grado_id', $request->grado_id);
        }

        $sections->orderBy('grado_id')->orderBy('nombre');

        return SectionResource::collection($sections->get());
    }

    public function store(StoreSectionRequest $request)
    {
        $validated = $request->validated();
        $section = Section::create($validated + ['estado' => 'ACTIVO']);

        if ($request->expectsJson()) {
            return (new SectionResource($section->fresh()->load(['grade', 'responsable'])))->response()->setStatusCode(201);
        }

        return redirect()->route('sections.index')->with('success', 'Paralelo creado correctamente.');
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

        return redirect()->route('sections.index')->with('success', 'Paralelo actualizado correctamente.');
    }

    public function destroy(Section $section)
    {
        $tieneMatriculados = false;

        if (Schema::hasColumn('courses', 'paralelo_id') && Schema::hasColumn('registrations', 'curso_id')) {
            $tieneMatriculados = DB::table('registrations')
                ->join('courses', 'registrations.curso_id', '=', 'courses.id')
                ->where('courses.paralelo_id', $section->id)
                ->where('registrations.estado', '!=', 'CANCELADA')
                ->exists();
        }

        if ($tieneMatriculados) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el paralelo porque tiene estudiantes matriculados.',
                ], 409);
            }

            return redirect()->route('sections.index')->with('error', 'No se puede eliminar el paralelo porque tiene estudiantes matriculados.');
        }

        DB::transaction(function () use ($section) {
            if (Schema::hasColumn('courses', 'paralelo_id')) {
                $cursoIds = DB::table('courses')->where('paralelo_id', $section->id)->pluck('id');

                if ($cursoIds->isNotEmpty() && Schema::hasColumn('registrations', 'curso_id')) {
                    DB::table('registrations')->whereIn('curso_id', $cursoIds)->delete();
                }

                DB::table('courses')->whereIn('id', $cursoIds)->delete();
            }

            $section->delete();
        });

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Paralelo eliminado correctamente.']);
        }

        return redirect()->route('sections.index')->with('success', 'Paralelo eliminado correctamente.');
    }
}
