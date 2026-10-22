@extends('layouts.app')
@section('titulo', 'Bitácora de Aula')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('diario.index', ['section_id' => $entrada->section_id]), 'texto' => 'Volver al diario'])
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $entrada->subject->nombre }} · {{ $entrada->fecha->format('d/m/Y') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $entrada->section->codigo ?? $entrada->section->nombre }} · Docente: {{ $entrada->teacher ? $entrada->teacher->nombres . ' ' . $entrada->teacher->apellidos : 'Administración' }}</p>
    </div>

    <div class="space-y-4 max-w-3xl">
        <div class="bg-card border border-border rounded-xl p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Avance curricular</h2>
            <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->avance_curricular }}</p>
        </div>
        @if($entrada->actividades_clase)
            <div class="bg-card border border-border rounded-xl p-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Actividades de clases</h2>
                <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->actividades_clase }}</p>
            </div>
        @endif
        @if($entrada->actividades_hogar)
            <div class="bg-card border border-border rounded-xl p-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Actividades para el hogar</h2>
                <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->actividades_hogar }}</p>
            </div>
        @endif
        @if($entrada->recursos)
            <div class="bg-card border border-border rounded-xl p-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Recursos</h2>
                <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->recursos }}</p>
            </div>
        @endif
        @if($entrada->observaciones)
            <div class="bg-card border border-border rounded-xl p-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Observaciones</h2>
                <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->observaciones }}</p>
            </div>
        @endif

        @if(auth()->user()->hasAnyRole(['Administrador', 'Director']) || (auth()->user()->hasRole('Docente') && auth()->user()->teacher && $entrada->teacher_id === auth()->user()->teacher->id))
            <form method="POST" action="{{ route('diario.destroy', $entrada) }}" onsubmit="return confirm('¿Eliminar esta bitácora?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg border border-red-300 text-red-700 text-sm hover:bg-red-50 transition-colors">Eliminar bitácora</button>
            </form>
        @endif
    </div>
@endsection
