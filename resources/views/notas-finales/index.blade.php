@extends('layouts.app')

@section('titulo', 'Notas Finales')

@section('sidebar')
    @if(auth()->user()->hasAnyRole('Estudiante,Padre/Tutor'))
        @include('dashboard.partials._sidebar-estudiante')
    @elseif(auth()->user()->hasRole('Docente'))
        @include('dashboard.partials._sidebar-docente')
    @else
        @include('dashboard.partials._sidebar-admin')
    @endif
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Notas Finales por Materia</h1>
            @if($student)
                <p class="text-sm text-muted-foreground mt-1">{{ $student->nombres }} {{ $student->apellidos }} — Bimestre {{ $bimestre }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if(!empty($estudiantes) && $estudiantes->count() > 1)
                <form method="GET" class="flex items-center gap-2">
                    <select name="student_id" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        @foreach($estudiantes as $hijo)
                            <option value="{{ $hijo->id }}" {{ $student && $student->id === $hijo->id ? 'selected' : '' }}>{{ $hijo->nombres }} {{ $hijo->apellidos }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <form method="GET" class="flex items-center gap-2">
                @if($student)
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                @endif
                <select name="bimestre" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    @for($b = 1; $b <= 4; $b++)
                        <option value="{{ $b }}" {{ $bimestre == $b ? 'selected' : '' }}>Bimestre {{ $b }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    @if($student)
        @if($notasFinales->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-muted-foreground text-sm">No hay notas registradas para el Bimestre {{ $bimestre }}.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($notasFinales as $nota)
                    @php
                        $color = $nota['aprobado'] ? 'text-success' : 'text-error';
                        $bg = $nota['aprobado'] ? 'bg-success/10' : 'bg-error/10';
                        $border = $nota['aprobado'] ? 'border-success/30' : 'border-error/30';
                    @endphp
                    <div class="bg-card border {{ $border }} rounded-xl overflow-hidden">
                        <div class="h-1.5 {{ $nota['aprobado'] ? 'bg-success' : 'bg-error' }}"></div>
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-foreground text-sm">{{ $nota['materia'] }}</h3>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $nota['cantidad_evaluaciones'] }} evaluación(es)</p>
                                </div>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $bg }} {{ $color }}">
                                    {{ $nota['aprobado'] ? 'Aprobado' : 'Reprobado' }}
                                </span>
                            </div>
                            <div class="text-center py-3">
                                <p class="text-3xl font-bold {{ $color }}">{{ number_format($nota['promedio'], 1) }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Nota Final — Bimestre {{ $bimestre }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $promedioGeneral = round($notasFinales->avg('promedio'), 1);
                $aprobadas = $notasFinales->where('aprobado', true)->count();
                $total = $notasFinales->count();
            @endphp
            <div class="mt-6 bg-card border border-border rounded-xl p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-sm text-muted-foreground">Promedio General</p>
                        <p class="text-2xl font-bold {{ $promedioGeneral >= 51 ? 'text-success' : 'text-error' }}">{{ $promedioGeneral }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Materias Aprobadas</p>
                        <p class="text-2xl font-bold text-foreground">{{ $aprobadas }}/{{ $total }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Estado General</p>
                        <p class="text-2xl font-bold {{ $aprobadas === $total ? 'text-success' : 'text-warning' }}">
                            {{ $aprobadas === $total ? 'Aprobado' : 'En curso' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron hijos registrados.</p>
        </div>
    @endif
@endsection
