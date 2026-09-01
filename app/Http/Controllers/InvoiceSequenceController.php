<?php

namespace App\Http\Controllers;

use App\Models\InvoiceSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceSequenceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex();
        }
        $sequences = InvoiceSequence::orderBy('codigo_sucursal')->orderBy('punto_venta')->paginate(15);
        return view('invoice-sequences.index', compact('sequences'));
    }

    private function apiIndex(): JsonResponse
    {
        return response()->json(['data' => InvoiceSequence::orderBy('codigo_sucursal')->get()]);
    }

    public function show(InvoiceSequence $invoiceSequence): JsonResponse
    {
        return response()->json(['data' => $invoiceSequence]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_sucursal' => ['required', 'string', 'max:10'],
            'punto_venta' => ['required', 'string', 'max:10'],
            'ultimo_numero' => ['sometimes', 'integer', 'min:0'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $exists = InvoiceSequence::where('codigo_sucursal', $validated['codigo_sucursal'])
            ->where('punto_venta', $validated['punto_venta'])->exists();
        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ya existe secuencia para esa sucursal/punto de venta.'], 409);
            }
            return back()->with('error', 'Ya existe secuencia para esa sucursal/punto de venta.');
        }

        $seq = InvoiceSequence::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $seq], 201);
        }
        return redirect()->route('invoice-sequences.index')->with('success', 'Secuencia creada.');
    }

    public function update(Request $request, InvoiceSequence $invoiceSequence)
    {
        $validated = $request->validate([
            'codigo_sucursal' => ['sometimes', 'string', 'max:10'],
            'punto_venta' => ['sometimes', 'string', 'max:10'],
            'ultimo_numero' => ['sometimes', 'integer', 'min:0'],
            'estado' => ['sometimes', 'in:ACTIVO,INACTIVO'],
        ]);

        $invoiceSequence->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $invoiceSequence->fresh()]);
        }
        return redirect()->route('invoice-sequences.index')->with('success', 'Secuencia actualizada.');
    }

    public function destroy(InvoiceSequence $invoiceSequence)
    {
        $invoiceSequence->delete();
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Secuencia eliminada.']);
        }
        return redirect()->route('invoice-sequences.index')->with('success', 'Secuencia eliminada.');
    }
}
