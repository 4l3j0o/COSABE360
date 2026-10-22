@extends('layouts.app')
@section('titulo', 'Mi Curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <a href="{{ route('docente.cursos') }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Mis Cursos
    </a>

    <div class="mb-4">
        <p class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground">{{ $curso->subject->nombre }}</p>
        <h1 class="text-lg font-bold text-foreground">{{ $curso->section->codigoCorto() }} · {{ $curso->section->grade?->nombre }} {{ $curso->section->nombre }}</h1>
        <p class="text-sm text-muted-foreground mt-0.5">{{ $curso->section->grade?->level?->nombre }}@if($curso->section->aula) · Aula {{ $curso->section->aula }}@endif · {{ $estudiantes }} estudiantes</p>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm divide-y divide-border overflow-hidden">
        <a href="{{ route('evaluations.index', $curso) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
            <span class="shrink-0 w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium text-foreground">Evaluaciones</span>
            <svg class="shrink-0 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('asistencias.create', ['section_id' => $curso->section_id, 'fecha' => now()->toDateString()]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
            <span class="shrink-0 w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3L22 4m-6 0H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V12"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium text-foreground">Pasar lista</span>
            <svg class="shrink-0 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('docente.asistencias', $curso) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
            <span class="shrink-0 w-9 h-9 rounded-lg bg-sky-500/10 text-sky-600 flex items-center justify-center">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium text-foreground">Historial de asistencia</span>
            <svg class="shrink-0 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('diario.index', ['section_id' => $curso->section_id, 'subject_id' => $curso->subject_id]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
            <span class="shrink-0 w-9 h-9 rounded-lg bg-violet-500/10 text-violet-600 flex items-center justify-center">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium text-foreground">Diario de aula</span>
            <svg class="shrink-0 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('conducta.index', ['section_id' => $curso->section_id, 'subject_id' => $curso->subject_id, 'contexto' => 1]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors">
            <span class="shrink-0 w-9 h-9 rounded-lg bg-amber-500/10 text-amber-700 flex items-center justify-center">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium text-foreground">Conducta</span>
            <svg class="shrink-0 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
@endsection
