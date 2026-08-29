@extends('layouts.app')

@section('titulo', 'Historial Académico')

@section('sidebar')
    @if(auth()->user()->hasAnyRole('Estudiante,Padre/Tutor'))
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('historial.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white/15 text-white font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Historial Académico
        </a>
    @else
        @include('dashboard.partials._sidebar-admin')
    @endif
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Historial Académico</h1>
            @if($student)
                <p class="text-sm text-muted-foreground mt-1">{{ $student->nombres }} {{ $student->apellidos }} — {{ $student->codigo }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            @if(!empty($estudiantes) && $estudiantes->count() > 1)
                <form method="GET" class="flex items-center gap-2">
                    @if($gestion_id)
                        <input type="hidden" name="gestion_id" value="{{ $gestion_id }}">
                    @endif
                    <select name="student_id" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        @foreach($estudiantes as $est)
                            <option value="{{ $est->id }}" {{ $student && $student->id === $est->id ? 'selected' : '' }}>{{ $est->nombres }} {{ $est->apellidos }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            @if($gestiones && $gestiones->count() > 0)
                <form method="GET" class="flex items-center gap-2">
                    @if($student)
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                    @endif
                    <select name="gestion_id" onchange="this.form.submit()" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Todas las gestiones</option>
                        @foreach($gestiones as $g)
                            <option value="{{ $g->id }}" {{ (string)$gestion_id === (string)$g->id ? 'selected' : '' }}>{{ $g->nombre }} ({{ $g->estado }})</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <a href="{{ route('historial.index', array_filter(['student_id' => $student?->id, 'gestion_id' => $gestion_id])) }}&export=pdf" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent transition-colors hidden">
                Exportar PDF
            </a>
        </div>
    </div>

    @if($student)
        @if($historial->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-muted-foreground text-sm">No hay calificaciones registradas para este estudiante.</p>
                <p class="text-xs text-muted-foreground mt-1">El historial mostrará todas las materias cursadas y promedios por gestión.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($historial->groupBy('gestion') as $gestion => $materias)
                    <div class="bg-card border border-border rounded-xl overflow-hidden">
                        <div class="bg-muted/50 px-5 py-3 border-b border-border flex items-center justify-between">
                            <h2 class="font-semibold text-foreground text-sm">Gestión {{ $gestion }}</h2>
                            <span class="text-xs text-muted-foreground">{{ $materias->count() }} materia(s)</span>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($materias as $item)
                                @php
                                    $color = $item['promedio_materia'] >= 51 ? 'text-success' : 'text-error';
                                    $bg = $item['promedio_materia'] >= 51 ? 'bg-success/10' : 'bg-error/10';
                                    $border = $item['promedio_materia'] >= 51 ? 'border-success/30' : 'border-error/30';
                                @endphp
                                <div class="border {{ $border }} rounded-xl overflow-hidden bg-card">
                                    <div class="h-1 {{ $item['promedio_materia'] >= 51 ? 'bg-success' : 'bg-error' }}"></div>
                                    <div class="p-4">
                                        <h3 class="font-semibold text-foreground text-sm">{{ $item['materia'] }}</h3>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $item['cantidad_notas'] }} nota(s) • Promedio {{ $item['promedio_materia'] }}</p>
                                        <div class="mt-3 space-y-2">
                                            @foreach($item['por_bimestre'] as $bim)
                                                <div class="flex items-center justify-between text-xs bg-muted/30 rounded-lg px-3 py-2">
                                                    <span class="text-muted-foreground">Bim {{ $bim['bimestre'] }}</span>
                                                    <span class="font-bold {{ $bim['promedio'] >= 51 ? 'text-success' : 'text-error' }}">{{ $bim['promedio'] }}</span>
                                                    <span class="text-muted-foreground">{{ $bim['notas']->count() }} ev.</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-center mt-3">
                                            <p class="text-2xl font-bold {{ $color }}">{{ number_format($item['promedio_materia'], 1) }}</p>
                                            <p class="text-xs text-muted-foreground">Promedio Materia</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $promedioGeneral = round($historial->avg('promedio_materia'), 1);
                $totalMaterias = $historial->count();
            @endphp
            <div class="mt-6 bg-card border border-border rounded-xl p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-sm text-muted-foreground">Promedio General Historial</p>
                        <p class="text-2xl font-bold {{ $promedioGeneral >= 51 ? 'text-success' : 'text-error' }}">{{ $promedioGeneral }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Total Materias</p>
                        <p class="text-2xl font-bold text-foreground">{{ $totalMaterias }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Gestiones</p>
                        <p class="text-2xl font-bold text-foreground">{{ $historial->pluck('gestion')->unique()->count() }}</p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <p class="text-muted-foreground text-sm">Seleccione un estudiante para ver su historial académico.</p>
        </div>
    @endif
@endsection
