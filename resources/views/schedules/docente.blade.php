@extends('layouts.app')
@section('titulo', 'Horario - ' . $teacher->nombres . ' ' . $teacher->apellidos)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 min-w-0">
            @include('layouts._volver', ['href' => route('teachers.index'), 'texto' => 'Volver a docentes'])
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">{{ $teacher->nombres }} {{ $teacher->apellidos }}</h1>
                <p class="text-sm text-muted-foreground">
                    Horario semanal de clases
                    @if($gestiones->isNotEmpty())
                        @foreach($gestiones as $gestion)
                            <span class="inline-flex items-center px-2 py-0.5 ml-1 rounded-full bg-primary/10 text-primary text-xs font-medium">Gestión {{ $gestion }}</span>
                        @endforeach
                    @endif
                </p>
            </div>
        </div>
    </div>

    @php
        $diasLabel = [
            'LUNES' => 'Lunes',
            'MARTES' => 'Martes',
            'MIERCOLES' => 'Miércoles',
            'JUEVES' => 'Jueves',
            'VIERNES' => 'Viernes',
            'SABADO' => 'Sábado',
        ];
        $claveFranja = fn ($s) => $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i');
    @endphp

    @if($schedules->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">Este docente no tiene clases asignadas en ningún curso.</p>
        </div>
    @else
        @foreach($grupos as $grupo)
            @include('schedules._tabla-docente', ['dias' => $dias, 'diasLabel' => $diasLabel, 'filas' => $grupo['filas'], 'tabla' => $grupo['tabla'], 'titulo' => $grupo['titulo']])
        @endforeach
    @endif
@endsection
