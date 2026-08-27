<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLevelRequest;
use App\Http\Requests\UpdateLevelRequest;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Schema;

class LevelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LevelResource::collection(Level::orderBy('orden')->get());
    }

    public function store(StoreLevelRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['estado'] = 'ACTIVO';

        $level = Level::create($validated);

        return (new LevelResource($level))->response()->setStatusCode(201);
    }

    public function show(Level $level): LevelResource
    {
        return new LevelResource($level);
    }

    public function update(UpdateLevelRequest $request, Level $level): LevelResource
    {
        $level->update($request->validated());

        return new LevelResource($level->fresh());
    }

    public function destroy(Level $level): JsonResponse
    {
        if (Schema::hasColumn('grades', 'nivel_id') && $level->grades()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el nivel porque tiene grados asociados.',
            ], 409);
        }

        $level->delete();

        return response()->json(['message' => 'Nivel eliminado correctamente.']);
    }
}
