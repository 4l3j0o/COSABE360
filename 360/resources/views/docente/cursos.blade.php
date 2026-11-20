@extends('layouts.app')
@section('titulo', 'Mis Cursos')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
    <div class="flex items-center justify-between gap-3 mb-4">
        <h1 class="text-lg font-bold text-foreground">Mis Cursos</h1>
        <span class="text-sm text-muted-foreground">{{ $cursos->count() }}</span>
    </div>

    @if($cursos->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-muted-foreground text-sm">No tiene cursos asignados. Solicite al administrador asignarle materias y cursos.</p>
        </div>
    @else
        @foreach($cursos->groupBy(fn ($c) => $c->section->grade?->level?->nombre ?? 'Sin nivel') as $nivel => $grupo)
            <h2 class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mt-5 mb-1.5 first:mt-0">{{ $nivel }}</h2>
            <div class="bg-card border border-border rounded-xl shadow-sm divide-y divide-border overflow-hidden">
                @foreach($grupo as $curso)
                    <a href="{{ route('docente.cursos.show', $curso) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
                        <span class="shrink-0 min-w-11 px-2 h-9 rounded-lg bg-muted text-foreground flex items-center justify-center text-xs font-bold">
                            {{ $curso->section->codigo ?? $curso->section->nombre }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-medium uppercase tracking-wider text-muted-foreground truncate">{{ $curso->subject->nombre }}</span>
                            <span class="block text-sm font-semibold text-foreground truncate">{{ $curso->section->codigoCorto() }} · {{ $curso->section->grade?->nombre }} {{ $curso->section->nombre }}</span>
                        </span>
                        <svg class="shrink-0 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
            </div>
        @endforeach
    @endif
@endsection
