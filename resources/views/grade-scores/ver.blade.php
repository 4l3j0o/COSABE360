@extends('layouts.app')
@section('titulo', 'Ver Calificaciones')

@section('sidebar')
    @if(auth()->user()->hasRole('Administrador'))
        @include('dashboard.partials._sidebar-admin')
    @else
        @include('dashboard.partials._sidebar-docente')
    @endif
@endsection

@section('contenido')
    <div class="max-w-4xl">
        <div class="mb-6">
            @php $cursoEvaluacion = $assessment->courseSubject?->course; @endphp
            <a href="{{ route('courses.calificaciones', $cursoEvaluacion) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Calificaciones - {{ $cursoEvaluacion?->nombre }}
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Calificaciones de la evaluación</h1>
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $assessment->subject?->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->tipo }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->fecha?->format('d/m/Y') ?? '—' }}</span>
            </div>
        </div>

        <div class="flex items-center justify-end mb-4">
            <a href="{{ route('calificaciones.show', $assessment) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary text-primary-foreground px-4 py-2 text-sm font-medium hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar calificaciones
            </a>
        </div>

        @include('grade-scores.partial-tabla-notas', ['modo' => 'ver', 'mostrarResumen' => true])
    </div>
@endsection
