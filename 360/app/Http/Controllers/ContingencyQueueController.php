<?php

namespace App\Http\Controllers;

use App\Models\ContingencyQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContingencyQueueController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }
        $queues = ContingencyQueue::with('electronicInvoice')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('proximo_intento')->paginate(15)->withQueryString();
        return view('contingency-queues.index', compact('queues'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $queues = ContingencyQueue::with('electronicInvoice')
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderBy('proximo_intento')->get();
        return response()->json(['data' => $queues]);
    }

    public function show(ContingencyQueue $contingencyQueue): JsonResponse
    {
        return response()->json(['data' => $contingencyQueue->load('electronicInvoice')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'electronic_invoice_id' => ['required', 'integer', 'exists:electronic_invoices,id'],
            'max_intentos' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'estado' => ['sometimes', 'in:PENDIENTE,ENVIANDO,COMPLETADO,FALLIDO'],
            'proximo_intento' => ['sometimes', 'date', 'nullable'],
        ]);

        $queue = ContingencyQueue::create($validated + ['intentos' => 0, 'estado' => $validated['estado'] ?? 'PENDIENTE']);

        if ($request->expectsJson()) {
            return response()->json(['data' => $queue->load('electronicInvoice')], 201);
        }
        return redirect()->route('contingency-queues.index')->with('success', 'Cola de contingencia creada.');
    }

    public function update(Request $request, ContingencyQueue $contingencyQueue)
    {
        $validated = $request->validate([
            'intentos' => ['sometimes', 'integer', 'min:0'],
            'max_intentos' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'estado' => ['sometimes', 'in:PENDIENTE,ENVIANDO,COMPLETADO,FALLIDO'],
            'ultimo_error' => ['nullable', 'string'],
            'proximo_intento' => ['nullable', 'date'],
        ]);

        $contingencyQueue->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $contingencyQueue->fresh()->load('electronicInvoice')]);
        }
        return redirect()->route('contingency-queues.index')->with('success', 'Cola actualizada.');
    }

    public function destroy(ContingencyQueue $contingencyQueue)
    {
        $contingencyQueue->delete();
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Cola eliminada.']);
        }
        return redirect()->route('contingency-queues.index')->with('success', 'Cola eliminada.');
    }
}
