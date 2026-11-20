<?php

namespace App\Http\Controllers;

use App\Models\RcIvaWithholding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RcIvaWithholdingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }
        $withholdings = RcIvaWithholding::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->when($request->query('tipo_sujeto'), fn ($q, $v) => $q->where('tipo_sujeto', $v))
            ->orderByDesc('fecha')->paginate(15)->withQueryString();
        return view('rc-iva-withholdings.index', compact('withholdings'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $withholdings = RcIvaWithholding::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha')->get();
        return response()->json(['data' => $withholdings]);
    }

    public function show(RcIvaWithholding $rcIvaWithholding): JsonResponse
    {
        return response()->json(['data' => $rcIvaWithholding]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'tipo_sujeto' => ['required', 'in:EMPLEADO,PROVEEDOR'],
            'sujeto_id' => ['required', 'integer', 'min:1'],
            'sujeto_nombre' => ['required', 'string', 'max:200'],
            'sujeto_documento' => ['required', 'string', 'max:20'],
            'nro_factura_origen' => ['nullable', 'string', 'max:50'],
            'base_imponible' => ['required', 'numeric', 'min:0'],
            'retencion_monto' => ['sometimes', 'numeric', 'min:0'],
            'comprobante_nro' => ['nullable', 'string', 'max:50'],
            'estado' => ['sometimes', 'in:REGISTRADA,CONFIRMADA,ANULADA'],
        ]);

        // Auto-cálculo 13% si no se envía retencion_monto
        if (!isset($validated['retencion_monto'])) {
            $validated['retencion_monto'] = RcIvaWithholding::calcularRetencion((float) $validated['base_imponible']);
        }

        $withholding = RcIvaWithholding::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $withholding], 201);
        }
        return redirect()->route('rc-iva-withholdings.index')->with('success', 'Retención RC-IVA registrada.');
    }

    public function update(Request $request, RcIvaWithholding $rcIvaWithholding)
    {
        $validated = $request->validate([
            'fecha' => ['sometimes', 'date'],
            'tipo_sujeto' => ['sometimes', 'in:EMPLEADO,PROVEEDOR'],
            'sujeto_nombre' => ['sometimes', 'string', 'max:200'],
            'sujeto_documento' => ['sometimes', 'string', 'max:20'],
            'base_imponible' => ['sometimes', 'numeric', 'min:0'],
            'retencion_monto' => ['sometimes', 'numeric', 'min:0'],
            'comprobante_nro' => ['nullable', 'string', 'max:50'],
            'estado' => ['sometimes', 'in:REGISTRADA,CONFIRMADA,ANULADA'],
        ]);

        // Recalcula si cambia base y no se envía retención
        if (isset($validated['base_imponible']) && !isset($validated['retencion_monto'])) {
            $validated['retencion_monto'] = RcIvaWithholding::calcularRetencion((float) $validated['base_imponible']);
        }

        $rcIvaWithholding->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $rcIvaWithholding->fresh()]);
        }
        return redirect()->route('rc-iva-withholdings.index')->with('success', 'Retención actualizada.');
    }

    public function destroy(RcIvaWithholding $rcIvaWithholding)
    {
        if ($rcIvaWithholding->estado === 'CONFIRMADA') {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'No se puede eliminar retención confirmada. Anule en su lugar.'], 409);
            }
            return back()->with('error', 'No se puede eliminar retención confirmada.');
        }

        $rcIvaWithholding->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Retención eliminada.']);
        }
        return redirect()->route('rc-iva-withholdings.index')->with('success', 'Retención eliminada.');
    }
}
