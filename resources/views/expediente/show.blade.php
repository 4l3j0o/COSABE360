@extends('layouts.app')
@section('titulo', 'Expediente - ' . $student->codigo)
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
@php
    $estilos = ['PENDIENTE' => 'bg-warning/10 text-warning', 'ENTREGADO' => 'bg-info/10 text-info', 'VERIFICADO' => 'bg-success/10 text-success'];
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('students.show', $student) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver al estudiante
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">Expediente Documental</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $student->apellidos }}, {{ $student->nombres }} · {{ $student->codigo }}</p>
        <p class="text-xs text-muted-foreground mt-1">Requisitos según R.M. 001/2022 (Art. 12) — Nivel: <strong>{{ $nivel ?? '—' }}</strong></p>
    </div>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-5">
    <div class="px-5 py-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Checklist de requisitos de inscripción</h3>
        <p class="text-xs text-muted-foreground mt-0.5">Marque el estado de cada documento del expediente.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50 text-left">
                    <th class="px-4 py-3 font-semibold text-foreground">Requisito</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Fecha entrega</th>
                    <th class="px-4 py-3 font-semibold text-foreground">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($checklist as $item)
                    <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $item->nombre }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $estilos[$item->estado] ?? 'bg-muted' }}">{{ $item->estado }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-muted-foreground text-xs">{{ $item->fecha_entrega?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('expediente.status', [$student, $item->documento_id ?? 0]) }}" class="flex gap-1 items-center">
                                @csrf
                                @if(! $item->documento_id)
                                    <input type="hidden" name="crear_tipo" value="{{ $item->tipo }}">
                                @endif
                                <select name="estado" class="px-2 py-1 border border-input rounded-lg text-xs bg-background text-foreground">
                                    @foreach(['PENDIENTE','ENTREGADO','VERIFICADO'] as $e)
                                        <option value="{{ $e }}" {{ $item->estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-2 py-1 rounded-lg text-xs bg-primary text-primary-foreground cursor-pointer">Guardar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@php
    $pendientes = $checklist->where('estado', '!=', 'VERIFICADO')->count();
    $verificados = $checklist->where('estado', 'VERIFICADO')->count();
@endphp
<div class="grid grid-cols-2 gap-4">
    <div class="bg-card border border-border rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-success">{{ $verificados }}</p>
        <p class="text-xs text-muted-foreground">Verificados</p>
    </div>
    <div class="bg-card border border-border rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-warning">{{ $pendientes }}</p>
        <p class="text-xs text-muted-foreground">Pendientes / incompletos</p>
    </div>
</div>
@endsection