<?php

namespace App\Http\Controllers;

use App\Models\SignificantEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignificantEventController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }
        $events = SignificantEvent::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha_inicio')->paginate(15)->withQueryString();
        return view('significant-events.index', compact('events'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $events = SignificantEvent::when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha_inicio')->get();
        return response()->json(['data' => $events]);
    }

    public function show(SignificantEvent $significantEvent): JsonResponse
    {
        return response()->json(['data' => $significantEvent]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_evento' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['required', 'string'],
            'documentos_soporte' => ['nullable', 'string'],
            'cufd_inicio' => ['nullable', 'string', 'max:100'],
            'estado' => ['sometimes', 'in:ABIERTO,CERRADO,REPORTADO'],
        ]);

        $event = SignificantEvent::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $event], 201);
        }
        return redirect()->route('significant-events.index')->with('success', 'Evento significativo registrado.');
    }

    public function update(Request $request, SignificantEvent $significantEvent)
    {
        $validated = $request->validate([
            'tipo_evento' => ['sometimes', 'string', 'max:50'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'motivo' => ['sometimes', 'string'],
            'documentos_soporte' => ['nullable', 'string'],
            'cufd_inicio' => ['nullable', 'string', 'max:100'],
            'estado' => ['sometimes', 'in:ABIERTO,CERRADO,REPORTADO'],
        ]);

        $significantEvent->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $significantEvent->fresh()]);
        }
        return redirect()->route('significant-events.index')->with('success', 'Evento actualizado.');
    }

    public function destroy(SignificantEvent $significantEvent)
    {
        $significantEvent->delete();
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Evento eliminado.']);
        }
        return redirect()->route('significant-events.index')->with('success', 'Evento eliminado.');
    }
}
