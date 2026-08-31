@extends('layouts.app')
@section('titulo', 'Calificaciones - ' . $course->nombre)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('asistencias.index') }}" class="shrink-0 inline-flex items-center gap-1.5 p-2 rounded-lg border border-border hover:bg-muted transition-colors text-muted-foreground hover:text-foreground" title="Volver a cursos">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">{{ $course->nombre }}</h1>
                <p class="text-sm text-muted-foreground">Calificaciones por evaluación · {{ $course->gestion?->nombre ?? '—' }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-card border border-border rounded-xl p-4 mb-4">
        <form method="GET" action="{{ route('courses.calificaciones', $course) }}" class="flex flex-wrap items-end gap-3">
            <div class="w-full min-w-0 sm:min-w-[180px] flex flex-col gap-1">
                <label for="materia" class="text-xs font-medium text-muted-foreground">Materia</label>
                <select name="materia" id="materia" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <option value="">Todas</option>
                    @foreach($materias as $mat)
                        <option value="{{ $mat->id }}" @selected((string) $materia === (string) $mat->id)>{{ $mat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full min-w-0 sm:min-w-[140px] flex flex-col gap-1">
                <label for="bimestre" class="text-xs font-medium text-muted-foreground">Trimestre</label>
                <select name="bimestre" id="bimestre" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <option value="">Todos</option>
                    @for($b = 1; $b <= 4; $b++)
                        <option value="{{ $b }}" @selected((string) $bimestre === (string) $b)>{{ $b }}º trimestre</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary text-primary-foreground px-4 py-2 text-sm font-medium hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filtrar
            </button>
            @if($materia || $bimestre)
                <a href="{{ route('courses.calificaciones', $course) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground hover:bg-muted transition-colors">
                    Limpiar filtros
                </a>
            @endif
        </form>
    </div>

    @if($assessments->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-muted-foreground text-sm">@if($materia || $bimestre) No hay evaluaciones que coincidan con los filtros seleccionados. @else Este curso no tiene evaluaciones registradas. @endif</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Materia</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Evaluación</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Tipo</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Notas</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $assessment->subject?->nombre }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $assessment->nombre }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->tipo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->fecha?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->grade_scores_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        @if(auth()->user()->hasAnyRole(['Docente', 'Administrador']))
                                            <a href="{{ route('calificaciones.ver', $assessment) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer inline-flex" title="Ver calificaciones">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
