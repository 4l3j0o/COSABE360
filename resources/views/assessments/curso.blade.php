@extends('layouts.app')
@section('titulo', 'Evaluaciones del curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <a href="{{ route('docente.cursos') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Mis Cursos
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Evaluaciones del curso</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Paralelo {{ $courseSubject->course->section->nombre ?? '—' }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Gestión {{ $courseSubject->course->gestion->nombre ?? '—' }}</span>
                @if($courseSubject->course->aula)
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Aula {{ $courseSubject->course->aula }}</span>
                @endif
                @if($dias->isEmpty())
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">- Sin horario</span>
                @else
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Días: {{ $dias->map(fn ($dia) => ucfirst(strtolower($dia)))->implode(' · ') }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('docente.evaluaciones.create', $courseSubject) }}" title="Nueva evaluación" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg bg-primary text-primary-foreground font-medium hover:opacity-90 transition-opacity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </a>
    </div>

    <p class="text-sm text-muted-foreground mb-4">Cada evaluación vale un porcentaje de la calificación final de la materia. En este curso el total debe sumar hasta 100%.</p>

    @if($assessments->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <p class="text-sm text-muted-foreground">Este curso aún no tiene evaluaciones. Registre la primera ("Nueva evaluación").</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 text-left">
                            <th class="px-4 py-3 font-semibold text-foreground">Nombre</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Tipo</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Bimestre</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Peso</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Fecha</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Notas</th>
                            <th class="px-4 py-3 text-right font-semibold text-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">{{ $assessment->nombre }}</p>
                                    @if($assessment->descripcion)
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $assessment->descripcion }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->tipo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->bimestre ? $assessment->bimestre . '°' : '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-primary">{{ round($assessment->peso * 100) }}%</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->fecha ? $assessment->fecha->format('d/m/Y') : '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $assessment->grade_scores_count }} calificación(es)</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('docente.calificaciones', ['courseSubject' => $courseSubject, 'assessment' => $assessment]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ $assessment->grade_scores_count > 0 ? 'Ver notas' : 'Registrar notas' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection