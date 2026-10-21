@extends('layouts.app')
@section('titulo', 'Regente - Cursos')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Gestionar Asistencia</h1>
        <p class="text-sm text-muted-foreground mt-1">Seleccione un curso para ver el consolidado de asistencia. Las licencias se gestionan en su propio módulo.</p>
    </div>
</div>

@if($cursos->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="text-sm text-muted-foreground">No hay cursos registrados.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm divide-y divide-border">
        @foreach($cursos as $curso)
            <a href="{{ route('regente.curso', $curso) }}"
               class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-muted/40 transition-colors group">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-foreground truncate">
                        {{ $curso->grade->nombre ?? '' }} {{ $curso->nombre ?? '' }}
                    </p>
                    <p class="text-xs text-muted-foreground">Gestión {{ $curso->gestion?->nombre ?? '' }}</p>
                </div>
                <span class="shrink-0 text-xs font-medium text-primary group-hover:underline">Ver consolidado →</span>
            </a>
        @endforeach
    </div>
@endif
@endsection