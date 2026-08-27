<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|View
    {
        $subjects = Subject::with('level');

        if ($request->filled('nivel_id')) {
            $subjects->where('nivel_id', $request->nivel_id);
        }

        if ($request->filled('estado')) {
            $subjects->where('estado', $request->estado);
        }

        $subjects->orderBy('nivel_id')->orderBy('nombre');

        if ($request->expectsJson()) {
            return SubjectResource::collection($subjects->get());
        }

        $levels = Level::orderBy('orden')->get();

        return view('subjects.index', [
            'subjects' => $subjects->paginate(10)->withQueryString(),
            'levels'   => $levels,
        ]);
    }

    public function store(StoreSubjectRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $validated['estado'] = 'ACTIVO';

        $subject = Subject::create($validated);

        if ($request->expectsJson()) {
            return (new SubjectResource($subject->fresh()->load('level')))->response()->setStatusCode(201);
        }

        return redirect()->route('subjects.index')->with('success', 'Materia creada correctamente.');
    }

    public function show(Subject $subject): SubjectResource
    {
        return new SubjectResource($subject->load('level'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): SubjectResource|RedirectResponse
    {
        $subject->update($request->validated());

        if ($request->expectsJson()) {
            return new SubjectResource($subject->fresh()->load('level'));
        }

        return redirect()->route('subjects.index')->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(Subject $subject): JsonResponse|RedirectResponse
    {
        $bloqueadoPor = [];

        // course_subject.materia_id RESTRICT — no borrar si está asignada a curso
        if (Schema::hasColumn('course_subject', 'materia_id') || Schema::hasColumn('course_subjects', 'materia_id')) {
            $tabla = Schema::hasColumn('course_subject', 'materia_id') ? 'course_subject' : 'course_subjects';
            if (DB::table($tabla)->where('materia_id', $subject->id)->exists()) {
                $bloqueadoPor[] = 'cursos asignados';
            }
        }

        // schedules.materia_id RESTRICT
        if (Schema::hasColumn('schedules', 'materia_id') && DB::table('schedules')->where('materia_id', $subject->id)->exists()) {
            $bloqueadoPor[] = 'horarios asignados';
        }

        if ($bloqueadoPor !== []) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar la materia porque tiene '.implode(' y ', $bloqueadoPor).'.',
                ], 409);
            }

            return redirect()->route('subjects.index')->with('error', 'No se puede eliminar la materia porque tiene '.implode(' y ', $bloqueadoPor).'.');
        }

        $subject->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Materia eliminada correctamente.']);
        }

        return redirect()->route('subjects.index')->with('success', 'Materia eliminada correctamente.');
    }
}
