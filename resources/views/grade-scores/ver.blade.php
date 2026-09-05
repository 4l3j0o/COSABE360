@extends('layouts.app')
@section('titulo', 'Ver Calificaciones')

@section('sidebar')
    @if(auth()->user()->hasRole('Administrador'))
        @include('dashboard.partials._sidebar-by-rol')
    @else
        @include('dashboard.partials._sidebar-docente')
    @endif
@endsection

@section('contenido')
    <div class="max-w-4xl">
        <div class="mb-6">
            @php $cursoEvaluacion = $assessment->courseSubject?->course; @endphp
            @if(auth()->user()->hasRole('Docente'))
                <a href="{{ route('evaluaciones.index') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Volver a evaluaciones
                </a>
            @else
                <a href="{{ route('courses.calificaciones', $cursoEvaluacion) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Volver a Calificaciones - {{ $cursoEvaluacion?->nombre }}
                </a>
            @endif
            <h1 class="text-xl font-bold text-foreground mt-2">Calificaciones de la evaluación</h1>
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $assessment->subject?->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->tipo }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->fecha?->format('d/m/Y') ?? '—' }}</span>
            </div>
        </div>

        @if(auth()->user()->hasAnyRole(['Docente', 'Administrador']))
            <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
                <a href="{{ route('evaluaciones.edit', $assessment) }}" class="inline-flex items-center gap-2 rounded-lg border border-border bg-background text-foreground px-4 py-2 text-sm font-medium hover:bg-muted transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('evaluaciones.destroy', $assessment) }}" onsubmit="return confirm('¿Estás seguro de eliminar esta evaluación?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-error/30 bg-error/5 text-error px-4 py-2 text-sm font-medium hover:bg-error/10 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                </form>
                <a href="{{ route('calificaciones.show', $assessment) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary text-primary-foreground px-4 py-2 text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar calificaciones
                </a>
            </div>
        @endif

        @include('grade-scores.partial-tabla-notas', ['modo' => 'ver', 'mostrarResumen' => true])
    </div>
@endsection
