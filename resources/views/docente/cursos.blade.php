@extends('layouts.app')
@section('titulo', 'Mis Cursos')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Mis Cursos</h1>
            <p class="text-sm text-muted-foreground">Ingrese a un curso y materia para ver sus estudiantes y evaluaciones</p>
        </div>
        <span class="shrink-0 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-card border border-border text-sm text-muted-foreground">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            {{ $cursos->count() }} curso(s) asignado(s)
        </span>
    </div>

    @if($cursos->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-muted-foreground text-sm">No tiene cursos asignados. Solicite al administrador asignarle materias y cursos.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cursos as $curso)
                <div class="bg-card border border-border rounded-xl p-5 shadow-sm hover:border-primary/40 hover:shadow-md transition-all">
<div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $curso->subject->nombre }}</p>
                            <h3 class="text-base font-bold text-foreground mt-1 truncate">{{ $curso->course->nombre }}</h3>
                            <p class="text-xs text-muted-foreground mt-1">{{ $curso->assessments_count }} evaluación(es)</p>
                        </div>
                        @if($curso->course->aula)
                            <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-lg bg-muted text-xs font-medium text-muted-foreground">Aula {{ $curso->course->aula }}</span>
                        @endif
                    </div>

                    {{-- Horario de la asignación (HU-41) --}}
                    @if($curso->schedules->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach($curso->schedules as $schedule)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-primary/5 border border-primary/10 text-[11px] font-medium text-primary">
                                    {{ \App\Models\Schedule::DIAS_LABEL[$schedule->dia] ?? $schedule->dia }}
                                    {{ \Carbon\Carbon::parse($schedule->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($schedule->hora_fin)->format('H:i') }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-t border-border grid grid-cols-5 gap-2">
                        <a href="{{ route('evaluations.index', $curso) }}" title="Evaluaciones por trimestre (HU-47)"
                            class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </a>
                        <a href="{{ route('docente.evaluaciones', $curso) }}" title="Evaluaciones"
                            class="inline-flex items-center justify-center p-2 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </a>
                        <a href="{{ route('asistencias.create', ['course_id' => $curso->course_id, 'fecha' => now()->toDateString()]) }}" title="Pasar lista"
                            class="inline-flex items-center justify-center p-2 rounded-lg border border-border text-foreground hover:bg-muted transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3L22 4m-6 0H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V12"/></svg>
                        </a>
                        <a href="{{ route('docente.asistencias', $curso) }}" title="Historial de Asistencia"
                            class="inline-flex items-center justify-center p-2 rounded-lg border border-border text-foreground hover:bg-muted transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </a>
                        <a href="{{ route('docente.notas-curso', $curso) }}" title="Notas / Calificaciones"
                            class="inline-flex items-center justify-center p-2 rounded-lg border border-border text-foreground hover:bg-muted transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection