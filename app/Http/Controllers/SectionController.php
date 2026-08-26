<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sections = Section::with(['grade', 'responsable']);

        if ($request->filled('nivel_id')) {
            $sections->whereHas('grade', fn ($q) => $q->where('nivel_id', $request->nivel_id));
        }

        if ($request->filled('grado_id')) {
            $sections->where('grado_id', $request->grado_id);
        }

        $sections->orderBy('grado_id')->orderBy('nombre');

        return response()->json(['data' => $sections->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grado_id'       => 'required|exists:grades,id',
            'nombre'         => ['required', 'string', 'max:10', Rule::unique('sections')->where(fn ($q) => $q->where('grado_id', $request->grado_id))],
            'responsable_id' => 'nullable|exists:teachers,id',
            'capacidad'      => 'nullable|integer|min:1|max:255',
        ]);

        $validated['estado'] = 'ACTIVO';

        $section = Section::create($validated);

        return response()->json(['data' => $section->fresh()->load(['grade', 'responsable'])], 201);
    }

    public function show(Section $section): JsonResponse
    {
        return response()->json(['data' => $section->load(['grade', 'responsable'])]);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $validated = $request->validate([
            'nombre'         => [
                'sometimes', 'string', 'max:10',
                Rule::unique('sections', 'nombre')
                    ->where('grado_id', $section->grado_id)
                    ->ignore($section->id),
            ],
            'responsable_id' => 'nullable|exists:teachers,id',
            'capacidad'      => 'sometimes|integer|min:1|max:255',
            'estado'         => ['sometimes', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $section->update($validated);

        return response()->json(['data' => $section->fresh()->load(['grade', 'responsable'])]);
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
