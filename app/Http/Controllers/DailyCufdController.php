<?php

namespace App\Http\Controllers;

use App\Models\DailyCufd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyCufdController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }
        $cufds = DailyCufd::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha_vigencia')->paginate(15)->withQueryString();
        return view('daily-cufds.index', compact('cufds'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $cufds = DailyCufd::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha_vigencia')->get();
        return response()->json(['data' => $cufds]);
    }

    public function show(DailyCufd $dailyCufd): JsonResponse
    {
        return response()->json(['data' => $dailyCufd]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cufd' => ['required', 'string', 'max:100', 'unique:daily_cufds,cufd'],
            'codigo_control' => ['required', 'string', 'max:50'],
            'fecha_vigencia' => ['required', 'date'],
            'motivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['sometimes', 'in:ACTIVO,VENCIDO,ANULADO'],
        ]);

        $cufd = DailyCufd::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $cufd], 201);
        }
        return redirect()->route('daily-cufds.index')->with('success', 'CUFD registrado.');
    }

    public function update(Request $request, DailyCufd $dailyCufd)
    {
        $validated = $request->validate([
            'cufd' => ['sometimes', 'string', 'max:100', 'unique:daily_cufds,cufd,'.$dailyCufd->id],
            'codigo_control' => ['sometimes', 'string', 'max:50'],
            'fecha_vigencia' => ['sometimes', 'date'],
            'motivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['sometimes', 'in:ACTIVO,VENCIDO,ANULADO'],
        ]);

        $dailyCufd->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $dailyCufd->fresh()]);
        }
        return redirect()->route('daily-cufds.index')->with('success', 'CUFD actualizado.');
    }

    public function destroy(DailyCufd $dailyCufd)
    {
        $dailyCufd->delete();
        if (request()->expectsJson()) {
            return response()->json(['message' => 'CUFD eliminado.']);
        }
        return redirect()->route('daily-cufds.index')->with('success', 'CUFD eliminado.');
    }
}
