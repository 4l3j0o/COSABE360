<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLevelRequest;
use App\Http\Requests\UpdateLevelRequest;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Schema;

class LevelController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|\Illuminate\Contracts\View\View
    {
        $levels = Level::withCount('grades')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('orden')
            ->get();

        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson()) {
            return LevelResource::collection($levels);
        }

        return view('levels.index', compact('levels'));
    }

    public function create(): \Illuminate\Contracts\View\View
    {
        return view('levels.create');
    }

    public function store(StoreLevelRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();
        $validated['estado'] = 'ACTIVO';

        $level = Level::create($validated);

        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson()) {
            return (new LevelResource($level->loadCount('grades')))->response()->setStatusCode(201);
        }

        return redirect()->route('levels.index')->with('success', 'Nivel creado correctamente.');
    }

    public function show(Level $level, Request $request): LevelResource|\Illuminate\Contracts\View\View
    {
        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson()) {
            return new LevelResource($level->loadCount('grades'));
        }

        $level->load('grades');
        $level->loadCount('grades');
        return view('levels.show', compact('level'));
    }

    public function edit(Level $level): \Illuminate\Contracts\View\View
    {
        return view('levels.edit', compact('level'));
    }

    public function update(UpdateLevelRequest $request, Level $level): LevelResource|\Illuminate\Http\RedirectResponse
    {
        $level->update($request->validated());

        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson()) {
            return new LevelResource($level->fresh()->loadCount('grades'));
        }

        return redirect()->route('levels.index')->with('success', 'Nivel actualizado correctamente.');
    }

    public function destroy(Level $level, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $column = Schema::hasColumn('grades', 'nivel_id') ? 'nivel_id' : (Schema::hasColumn('grades', 'level_id') ? 'level_id' : null);
        if ($column && $level->grades()->exists()) {
            $message = 'No se puede eliminar el nivel porque tiene grados asociados.';
            if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $message], 409);
            }
            return redirect()->route('levels.index')->with('error', $message);
        }

        $level->delete();

        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => 'Nivel eliminado correctamente.']);
        }

        return redirect()->route('levels.index')->with('success', 'Nivel eliminado correctamente.');
    }
}
