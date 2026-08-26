<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $grades = Grade::with('level')
            ->when($request->query('nivel_id'), fn ($q, $v) => $q->where('nivel_id', $v))
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('nivel_id')
            ->orderBy('orden')
            ->get();

        return response()->json(['data' => $grades]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nivel_id' => ['required', 'integer', 'exists:levels,id'],
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grades', 'nombre')->where('nivel_id', $request->integer('nivel_id')),
            ],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $grade = Grade::create($validated + ['estado' => 'ACTIVO']);

        return response()->json(['data' => $grade->load('level')], 201);
    }

    public function show(Grade $grade): JsonResponse
    {
        return response()->json(['data' => $grade->load('level')]);
    }

    public function update(Request $request, Grade $grade): JsonResponse
    {
        $validated = $request->validate([
            'nivel_id' => ['sometimes', 'integer', 'exists:levels,id'],
            'nombre' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('grades', 'nombre')
                    ->where('nivel_id', $request->integer('nivel_id', $grade->nivel_id))
                    ->ignore($grade->id),
            ],
            'orden' => ['sometimes', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $grade->update($validated);

        return response()->json(['data' => $grade->fresh('level')]);
    }

    public function destroy(Grade $grade): JsonResponse
    {
        $bloqueadoPor = [];

        if (Schema::hasColumn('sections', 'grado_id') && $grade->sections()->exists()) {
            $bloqueadoPor[] = 'paralelos asociados';
        }

        if (Schema::hasColumn('courses', 'grado_id') && Schema::hasColumn('registrations', 'curso_id')) {
            $tieneMatriculados = DB::table('registrations')
                ->join('courses', 'registrations.curso_id', '=', 'courses.id')
                ->where('courses.grado_id', $grade->id)
                ->where('registrations.estado', '!=', 'CANCELADA')
                ->exists();

            if ($tieneMatriculados) {
                $bloqueadoPor[] = 'estudiantes matriculados';
            }
        }

        if ($bloqueadoPor !== []) {
            return response()->json([
                'message' => 'No se puede eliminar el grado porque tiene '.implode(' y ', $bloqueadoPor).'.',
            ], 409);
        }

        $grade->delete();

        return response()->json(['message' => 'Grado eliminado correctamente.']);
    }
}
