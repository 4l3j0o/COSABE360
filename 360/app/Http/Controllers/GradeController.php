<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $levels = Level::where('estado', '!=', 'ELIMINADO')->orderBy('orden')->get();
        $grades = Grade::with('level')
            ->where('estado', '!=', 'ELIMINADO')
            ->when($request->query('level_id'), fn ($q, $v) => $q->where('level_id', $v))
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('level_id')
            ->orderBy('orden')
            ->paginate(10)
            ->withQueryString();

        $nivelActual = $request->query('level_id')
            ? Level::find($request->query('level_id'))
            : null;

        return view('grades.index', compact('grades', 'levels', 'nivelActual'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $grades = Grade::with('level')
            ->where('estado', '!=', 'ELIMINADO')
            ->when($request->query('level_id'), fn ($q, $v) => $q->where('level_id', $v))
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('level_id')
            ->orderBy('orden')
            ->get();

        return response()->json(['data' => $grades]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grades', 'nombre')->where('level_id', $request->integer('level_id')),
            ],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        Grade::create($validated + ['estado' => 'ACTIVO']);

        if ($request->expectsJson()) {
            $grade = Grade::with('level')->latest()->first();
            return response()->json(['data' => $grade], 201);
        }

        return $this->deVuelta('grados.index', ['level_id', 'estado', 'page'], 'Grado creado correctamente.');
    }

    public function show(Grade $grade)
    {
        if (request()->expectsJson()) {
            return response()->json(['data' => $grade->load('level')]);
        }

        return view('grades.show', ['grade' => $grade->load('level')]);
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'level_id' => ['sometimes', 'integer', 'exists:levels,id'],
            'nombre' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('grades', 'nombre')
                    ->where('level_id', $request->integer('level_id', $grade->level_id))
                    ->ignore($grade->id),
            ],
            'orden' => ['sometimes', 'integer', 'min:1'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $grade->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $grade->fresh('level')]);
        }

        return $this->deVuelta('grados.index', ['level_id', 'estado', 'page'], 'Grado actualizado correctamente.');
    }

    public function destroy(Grade $grade)
    {
        $bloqueadoPor = [];

        if (Schema::hasColumn('sections', 'grade_id') && $grade->sections()->exists()) {
            $bloqueadoPor[] = 'paralelos asociados';
        }

        if (Schema::hasColumn('sections', 'grade_id') && Schema::hasColumn('registrations', 'section_id')) {
            $tieneMatriculados = DB::table('registrations')
                ->join('sections', 'registrations.section_id', '=', 'sections.id')
                ->where('sections.grade_id', $grade->id)
                ->where('registrations.estado', '!=', 'CANCELADA')
                ->exists();

            if ($tieneMatriculados) {
                $bloqueadoPor[] = 'estudiantes matriculados';
            }
        }

        if ($bloqueadoPor !== []) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el grado porque tiene '.implode(' y ', $bloqueadoPor).'.',
                ], 409);
            }

            return $this->deVuelta('grados.index', ['level_id', 'estado', 'page'], 'No se puede eliminar el grado porque tiene '.implode(' y ', $bloqueadoPor).'.', 'error');
        }

        $grade->update(['estado' => 'ELIMINADO']);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Grado eliminado correctamente.']);
        }

        return $this->deVuelta('grados.index', ['level_id', 'estado', 'page'], 'Grado eliminado correctamente.');
    }
}
