<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SectionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $sections = Section::with(['grade', 'responsable']);

        if ($request->filled('nivel_id')) {
            $sections->whereHas('grade', fn ($q) => $q->where('nivel_id', $request->nivel_id));
        }

        if ($request->filled('grado_id')) {
            $sections->where('grado_id', $request->grado_id);
        }

        $sections->orderBy('grado_id')->orderBy('nombre');

        return SectionResource::collection($sections->get());
    }

    public function store(StoreSectionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['estado'] = 'ACTIVO';

        $section = Section::create($validated);

        return (new SectionResource($section->fresh()->load(['grade', 'responsable'])))->response()->setStatusCode(201);
    }

    public function show(Section $section): SectionResource
    {
        return new SectionResource($section->load(['grade', 'responsable']));
    }

    public function update(UpdateSectionRequest $request, Section $section): SectionResource
    {
        $section->update($request->validated());

        return new SectionResource($section->fresh()->load(['grade', 'responsable']));
    }

    public function destroy(Section $section): JsonResponse
    {
        $bloqueadoPor = [];

        if (Schema::hasColumn('courses', 'paralelo_id') && Schema::hasColumn('registrations', 'curso_id')) {
            $tieneMatriculados = DB::table('registrations')
                ->join('courses', 'registrations.curso_id', '=', 'courses.id')
                ->where('courses.paralelo_id', $section->id)
                ->where('registrations.estado', '!=', 'CANCELADA')
                ->exists();

            if ($tieneMatriculados) {
                $bloqueadoPor[] = 'estudiantes matriculados';
            }
        }

        if ($bloqueadoPor !== []) {
            return response()->json([
                'message' => 'No se puede eliminar el paralelo porque tiene '.implode(' y ', $bloqueadoPor).'.',
            ], 409);
        }

        $section->delete();

        return response()->json(['message' => 'Paralelo eliminado correctamente.']);
    }
}
