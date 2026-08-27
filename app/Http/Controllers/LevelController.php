<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $levels = Level::withCount('grades')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('orden')
            ->get();

        return view('levels.index', compact('levels'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $levels = Level::withCount('grades')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('orden')
            ->get();

        return response()->json(['data' => $levels]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('levels', 'nombre'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        Level::create($validated + ['estado' => 'ACTIVO']);

        if ($request->expectsJson()) {
            $level = Level::withCount('grades')->latest()->first();
            return response()->json(['data' => $level], 201);
        }

        return redirect()->route('levels.index')->with('success', 'Nivel educativo creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Level $level)
    {
        $validated = $request->validate([
            'nombre' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('levels', 'nombre')->ignore($level->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'orden' => ['sometimes', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $level->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $level->fresh()->loadCount('grades')]);
        }

        return redirect()->route('levels.index')->with('success', 'Nivel educativo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Level $level)
    {
        if (Schema::hasColumn('grades', 'level_id') && $level->grades()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el nivel porque tiene grados asociados.',
                ], 409);
            }

            return redirect()->route('levels.index')->with('error', 'No se puede eliminar el nivel porque tiene grados asociados.');
        }

        $level->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Nivel eliminado correctamente.']);
        }

        return redirect()->route('levels.index')->with('success', 'Nivel educativo eliminado correctamente.');
    }
}
