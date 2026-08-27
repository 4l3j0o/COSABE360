<?php

namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentGuardianController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $padres = ParentGuardian::withCount('students')
            ->when($request->query('buscar'), function ($q, $b) {
                $buscable = addcslashes($b, '%_');
                $q->where(fn ($w) =>
                    $w->where('nombres', 'like', "%{$buscable}%")
                      ->orWhere('apellidos', 'like', "%{$buscable}%")
                      ->orWhere('numero_documento', 'like', "%{$buscable}%")
                      ->orWhere('correo', 'like', "%{$buscable}%")
                );
            })
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('apellidos')
            ->paginate(10)
            ->withQueryString();

        return view('parent_guardians.index', compact('padres'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $padres = ParentGuardian::withCount('students')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('apellidos')
            ->get();

        return response()->json(['data' => $padres]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nombres'   => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'tipo_documento' => ['sometimes', 'string', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'complemento'     => ['nullable', 'string', 'max:5'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'correo'     => [
                'nullable',
                'string',
                'email',
                'max:150',
                Rule::unique('parent_guardians', 'correo'),
            ],
            'direccion'  => ['nullable', 'string', 'max:255'],
            'ocupacion'  => ['nullable', 'string', 'max:100'],
            'estado'     => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';

        try {
            $padre = ParentGuardian::create($validated + ['estado' => 'ACTIVO']);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'numero_documento') && str_contains($e->getMessage(), 'complemento')) {
                return back()
                    ->withInput()
                    ->withErrors(['numero_documento' => 'Ya existe un padre/tutor registrado con ese documento y complemento.']);
            }
            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $padre->loadCount('students')], 201);
        }

        return redirect()->route('padres-tutores.index')->with('success', 'Padre/tutor registrado correctamente.');
    }

    public function update(Request $request, ParentGuardian $parentGuardian): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nombres'   => ['sometimes', 'string', 'max:150'],
            'apellidos' => ['sometimes', 'string', 'max:150'],
            'tipo_documento' => ['sometimes', 'string', 'in:CI,PASAPORTE,OTRO'],
            'numero_documento' => ['sometimes', 'string', 'max:20'],
            'complemento'     => ['nullable', 'string', 'max:5'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'correo'     => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:150',
                Rule::unique('parent_guardians', 'correo')->ignore($parentGuardian->id),
            ],
            'direccion'  => ['nullable', 'string', 'max:255'],
            'ocupacion'  => ['nullable', 'string', 'max:100'],
            'estado'     => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $validated['complemento'] = $validated['complemento'] ?? '';
        $parentGuardian->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $parentGuardian->fresh()->loadCount('students')]);
        }

        return redirect()->route('padres-tutores.index')->with('success', 'Padre/tutor actualizado correctamente.');
    }

    public function destroy(ParentGuardian $parentGuardian): RedirectResponse|JsonResponse
    {
        if ($parentGuardian->students()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el padre/tutor porque está asociado a uno o más estudiantes.',
                ], 409);
            }

            return redirect()->route('padres-tutores.index')->with('error', 'No se puede eliminar el padre/tutor porque está asociado a uno o más estudiantes.');
        }

        $parentGuardian->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Padre/tutor eliminado correctamente.']);
        }

        return redirect()->route('padres-tutores.index')->with('success', 'Padre/tutor eliminado correctamente.');
    }
}
