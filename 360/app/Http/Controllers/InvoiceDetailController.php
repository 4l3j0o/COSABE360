<?php

namespace App\Http\Controllers;

use App\Models\InvoiceDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceDetailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $details = InvoiceDetail::with('electronicInvoice')
            ->when($request->query('electronic_invoice_id'), fn ($q, $v) => $q->where('electronic_invoice_id', $v))
            ->orderBy('nro_registro')->get();
        return response()->json(['data' => $details]);
    }

    public function show(InvoiceDetail $invoiceDetail): JsonResponse
    {
        return response()->json(['data' => $invoiceDetail->load('electronicInvoice')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'electronic_invoice_id' => ['required', 'integer', 'exists:electronic_invoices,id'],
            'nro_registro' => ['required', 'integer', 'min:1'],
            'codigo_servicio' => ['required', 'string', 'max:20'],
            'descripcion' => ['required', 'string', 'max:200'],
            'cantidad' => ['sometimes', 'numeric', 'min:0'],
            'unidad_medida' => ['required', 'string', 'max:20'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'monto_descuento' => ['sometimes', 'numeric', 'min:0'],
            'sub_total' => ['required', 'numeric', 'min:0'],
            'iva_tipo' => ['sometimes', 'in:EXENTO'],
            'it_tipo' => ['sometimes', 'in:EXENTO'],
            'leyenda' => ['nullable', 'string', 'max:255'],
        ]);

        $detail = InvoiceDetail::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $detail], 201);
        }
        return back()->with('success', 'Detalle creado.');
    }

    public function update(Request $request, InvoiceDetail $invoiceDetail)
    {
        $validated = $request->validate([
            'descripcion' => ['sometimes', 'string', 'max:200'],
            'cantidad' => ['sometimes', 'numeric', 'min:0'],
            'precio_unitario' => ['sometimes', 'numeric', 'min:0'],
            'sub_total' => ['sometimes', 'numeric', 'min:0'],
            'leyenda' => ['nullable', 'string', 'max:255'],
        ]);

        $invoiceDetail->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $invoiceDetail->fresh()]);
        }
        return back()->with('success', 'Detalle actualizado.');
    }

    public function destroy(InvoiceDetail $invoiceDetail)
    {
        $invoiceDetail->delete();
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Detalle eliminado.']);
        }
        return back()->with('success', 'Detalle eliminado.');
    }

    // Stubs para resource compatibilidad
    public function create() { return response()->json(['message' => 'Use store']); }
    public function edit(InvoiceDetail $invoiceDetail) { return response()->json(['data' => $invoiceDetail]); }
}
