@extends('layouts.app')

@section('titulo', 'Mis Calificaciones')

@section('sidebar')
    @if(auth()->user()->hasRole('Estudiante'))
        @include('dashboard.partials._sidebar-estudiante')
    @else
        @include('dashboard.partials._sidebar-by-rol')
    @endif
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Calificaciones</h1>
            @if($student)
                <p class="text-sm text-muted-foreground mt-1">{{ $student->nombres }} {{ $student->apellidos }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if($estudiantes && $estudiantes->count() > 1)
                <form method="GET" action="{{ route('calificaciones.index') }}" class="flex items-center gap-2">
                    <label class="text-sm text-muted-foreground">Hijo:</label>
                    <select name="student_id" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @foreach($estudiantes as $hijo)
                            <option value="{{ $hijo->id }}" {{ $student && $student->id === $hijo->id ? 'selected' : '' }}>{{ $hijo->nombres }} {{ $hijo->apellidos }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <form method="GET" action="{{ route('calificaciones.index') }}" class="flex items-center gap-2">
                @if($student)
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                @endif
                <select name="bimestre" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todos los bimestres</option>
                    @for($b = 1; $b <= 4; $b++)
                        <option value="{{ $b }}" {{ $bimestre == $b ? 'selected' : '' }}>Bimestre {{ $b }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    @if($student)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="h-1.5 bg-primary"></div>
                <div class="p-5">
                    <p class="text-sm text-muted-foreground">Promedio General</p>
                    <p class="text-2xl font-bold text-primary mt-1">{{ number_format($promedioGeneral, 1) }}</p>
                </div>
            </div>
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="h-1.5 bg-primary/60"></div>
                <div class="p-5">
                    <p class="text-sm text-muted-foreground">Materias</p>
                    <p class="text-2xl font-bold text-foreground mt-1">{{ $calificacionesPorMateria->count() }}</p>
                </div>
            </div>
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="h-1.5 bg-primary/40"></div>
                <div class="p-5">
                    <p class="text-sm text-muted-foreground">Bimestre</p>
                    <p class="text-2xl font-bold text-foreground mt-1">{{ $bimestre ? 'Bimestre '.$bimestre : 'Todos' }}</p>
                </div>
            </div>
        </div>

        @if($calificacionesPorMateria->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-muted-foreground text-sm">No hay calificaciones registradas{{ $bimestre ? ' para el Bimestre '.$bimestre : '' }}.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($calificacionesPorMateria as $materia)
                    @php
                        $promedio = $materia['promedio'];
                        $colorPromedio = $promedio >= 70 ? 'text-success' : ($promedio >= 51 ? 'text-warning' : 'text-error');
                    @endphp
                    <div class="bg-card border border-border rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-border bg-muted/30">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </span>
                                <div>
                                    <h3 class="font-semibold text-foreground text-sm">{{ $materia['materia'] }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ $materia['cantidad_notas'] }} evaluación(es)</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $colorPromedio }}">{{ number_format($promedio, 1) }}</p>
                                <p class="text-xs text-muted-foreground">Promedio</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border bg-muted/20">
                                        <th class="text-left px-5 py-2.5 font-medium text-muted-foreground text-xs">Evaluación</th>
                                        <th class="text-left px-5 py-2.5 font-medium text-muted-foreground text-xs">Tipo</th>
                                        <th class="text-center px-5 py-2.5 font-medium text-muted-foreground text-xs">Bimestre</th>
                                        <th class="text-center px-5 py-2.5 font-medium text-muted-foreground text-xs">Peso</th>
                                        <th class="text-center px-5 py-2.5 font-medium text-muted-foreground text-xs">Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($materia['notas'] as $nota)
                                        @php
                                            $colorNota = $nota['nota'] >= 70 ? 'text-success' : ($nota['nota'] >= 51 ? 'text-warning' : 'text-error');
                                            $tipoBadge = match($nota['tipo']) {
                                                'EXAMEN' => 'bg-primary/10 text-primary',
                                                'TAREA' => 'bg-success/10 text-success',
                                                'PROYECTO' => 'bg-warning/10 text-warning',
                                                'PARTICIPACION' => 'bg-info/10 text-info',
                                                default => 'bg-muted text-muted-foreground',
                                            };
                                        @endphp
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors">
                                            <td class="px-5 py-3 font-medium text-foreground">{{ $nota['nombre'] }}</td>
                                            <td class="px-5 py-3">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $tipoBadge }}">{{ $nota['tipo'] }}</span>
                                            </td>
                                            <td class="px-5 py-3 text-center text-muted-foreground">{{ $nota['bimestre'] }}°</td>
                                            <td class="px-5 py-3 text-center text-muted-foreground">{{ number_format($nota['peso'], 0) }}%</td>
                                            <td class="px-5 py-3 text-center">
                                                <span class="font-semibold {{ $colorNota }}">{{ number_format($nota['nota'], 1) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron hijos registrados o no hay calificaciones disponibles.</p>
        </div>
    @endif
@endsection
