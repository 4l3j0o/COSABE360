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
        $subjects = Subject::with('level')->where('estado', '!=', 'ELIMINADO');

        if ($request->filled('level_id')) {
            $subjects->where('level_id', $request->level_id);
        }

        if ($request->filled('estado')) {
            $subjects->where('estado', $request->estado);
        }

        $subjects->orderBy('level_id')->orderBy('nombre');

        if ($request->expectsJson()) {
            return SubjectResource::collection($subjects->get());
        }

        $levels = Level::where('estado', '!=', 'ELIMINADO')->orderBy('orden')->get();

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

    public function show(Subject $subject): SubjectResource|View
    {
        $subject->load('level');

        if (request()->expectsJson()) {
            return new SubjectResource($subject);
        }

        $levels = Level::where('estado', '!=', 'ELIMINADO')->orderBy('orden')->get();

        return view('subjects.show', [
            'subject' => $subject,
            'levels'  => $levels,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): SubjectResource|JsonResponse|RedirectResponse
    {
        // Una materia ELIMINADA no puede editarse ni reactivarse
        if ($subject->estado === 'ELIMINADO') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede editar una materia eliminada.',
                ], 409);
            }

            return redirect()->route('subjects.index')->with('error', 'No se puede editar una materia eliminada.');
        }

        $subject->update($request->validated());

        if ($request->expectsJson()) {
            return new SubjectResource($subject->fresh()->load('level'));
        }

        return redirect()->route('subjects.index')->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(Subject $subject): JsonResponse|RedirectResponse
    {
        $bloqueadoPor = [];

        // course_subject.subject_id RESTRICT — no borrar si está asignada a curso
        if (Schema::hasColumn('course_subject', 'subject_id') || Schema::hasColumn('course_subjects', 'subject_id')) {
            $tabla = Schema::hasColumn('course_subject', 'subject_id') ? 'course_subject' : 'course_subjects';
            if (DB::table($tabla)->where('subject_id', $subject->id)->exists()) {
                $bloqueadoPor[] = 'cursos asignados';
            }
        }

        // schedules.subject_id RESTRICT
        if (Schema::hasColumn('schedules', 'subject_id') && DB::table('schedules')->where('subject_id', $subject->id)->exists()) {
            $bloqueadoPor[] = 'horarios asignados';
        }

        // CA-14-03: assessments.subject_id — no borrar si tiene evaluaciones (y por tanto calificaciones registradas)
        if (Schema::hasColumn('assessments', 'subject_id') && DB::table('assessments')->where('subject_id', $subject->id)->exists()) {
            $bloqueadoPor[] = 'evaluaciones o calificaciones registradas';
        }

        if ($bloqueadoPor !== []) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar la materia porque tiene '.implode(' y ', $bloqueadoPor).'.',
                ], 409);
            }

            return redirect()->route('subjects.index')->with('error', 'No se puede eliminar la materia porque tiene '.implode(' y ', $bloqueadoPor).'.');
        }

        // Borrado lógico: se marca como ELIMINADO en lugar de borrar físicamente.
        $subject->update(['estado' => 'ELIMINADO']);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Materia eliminada correctamente.']);
        }

        return redirect()->route('subjects.index')->with('success', 'Materia eliminada correctamente.');
    }
}
