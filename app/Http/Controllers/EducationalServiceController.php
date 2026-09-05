<?php

namespace App\Http\Controllers;

use App\Models\EducationalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EducationalServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $services = EducationalService::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where('descripcion', 'like', "%{$v}%")->orWhere('codigo_sin', 'like', "%{$v}%"))
            ->orderBy('codigo_sin')
            ->paginate(15)->withQueryString();

        return view('educational-services.index', compact('services'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $services = EducationalService::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('codigo_sin')->get();
        return response()->json(['data' => $services]);
    }

    public function show(EducationalService $educationalService): JsonResponse
    {
        return response()->json(['data' => $educationalService]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_sin' => ['required', 'string', 'max:20', Rule::unique('educational_services', 'codigo_sin')],
            'descripcion' => ['required', 'string', 'max:200'],
            'unidad_medida' => ['sometimes', 'string', 'max:20'],
            'iva_tipo' => ['sometimes', 'in:EXENTO'],
            'iva_tasa' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'it_tipo' => ['sometimes', 'in:EXENTO'],
            'it_tasa' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'leyenda_factura' => ['nullable', 'string', 'max:255'],
            'aplica_exento_educacion' => ['sometimes', 'boolean'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $service = EducationalService::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $service], 201);
        }

        return redirect()->route('educational-services.index')->with('success', 'Servicio educativo creado correctamente.');
    }

    public function update(Request $request, EducationalService $educationalService)
    {
        $validated = $request->validate([
            'codigo_sin' => ['sometimes', 'string', 'max:20', Rule::unique('educational_services', 'codigo_sin')->ignore($educationalService->id)],
            'descripcion' => ['sometimes', 'string', 'max:200'],
            'unidad_medida' => ['sometimes', 'string', 'max:20'],
            'iva_tipo' => ['sometimes', 'in:EXENTO'],
            'iva_tasa' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'it_tipo' => ['sometimes', 'in:EXENTO'],
            'it_tasa' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'leyenda_factura' => ['nullable', 'string', 'max:255'],
            'aplica_exento_educacion' => ['sometimes', 'boolean'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $educationalService->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $educationalService->fresh()]);
        }

        return redirect()->route('educational-services.index')->with('success', 'Servicio educativo actualizado.');
    }

    public function destroy(EducationalService $educationalService)
    {
        $educationalService->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Servicio eliminado.']);
        }

        return redirect()->route('educational-services.index')->with('success', 'Servicio eliminado.');
    }
}
