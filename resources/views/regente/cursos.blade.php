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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($cursos as $curso)
            <a href="{{ route('regente.curso', $curso) }}" class="bg-card border border-border rounded-xl p-5 shadow-sm hover:border-primary/40 hover:shadow-md transition-all">
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $curso->grade->nombre ?? '' }} {{ $curso->nombre ?? '' }}</p>
                <h3 class="text-base font-bold text-foreground mt-1">{{ $curso->nombre }}</h3>
                <p class="text-xs text-muted-foreground mt-1">{{ $curso->gestion?->nombre ?? '' }}</p>
            </a>
        @endforeach
    </div>
@endif
@endsection