<?php

namespace App\Http\Controllers;

use App\Models\OperationHistory;
use App\Models\User;
use Illuminate\Http\Request;

class OperationHistoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->puede('auditoria.ver'), 403);

        $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'modulo' => ['nullable', 'string', 'max:50'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $query = OperationHistory::with(['user.rol'])
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->modulo, fn ($q, $v) => $q->where('modulo', $v))
            ->when($request->fecha_desde, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->fecha_hasta, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at');

        $historial = $query->paginate(10)->withQueryString();

        $usuarios = User::with('rol')->orderBy('nombre')->get();
        $modulos = OperationHistory::select('modulo')->distinct()->pluck('modulo')->filter()->values();

        if ($request->expectsJson()) {
            return response()->json(['data' => $historial]);
        }

        return view('auditoria.index', compact('historial', 'usuarios', 'modulos'));
    }

    public function show(Request $request, OperationHistory $operationHistory)
    {
        abort_unless($request->user()->puede('auditoria.ver'), 403);

        $operationHistory->load(['user.rol']);
        if ($request->expectsJson()) {
            return response()->json(['data' => $operationHistory]);
        }
        return view('auditoria.show', compact('operationHistory'));
    }
}
