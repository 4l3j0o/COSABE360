@extends('layouts.app')
@section('titulo', 'Mi Horario')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-foreground">Mi Horario</h1>
            <p class="text-sm text-muted-foreground">
                Horario semanal de todas sus clases
                @if($gestiones->isNotEmpty())
                    @foreach($gestiones as $gestion)
                        <span class="inline-flex items-center px-2 py-0.5 ml-1 rounded-full bg-primary/10 text-primary text-xs font-medium">Gestión {{ $gestion }}</span>
                    @endforeach
                @endif
            </p>
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
            <p class="text-muted-foreground text-sm">No tiene clases asignadas en ningún curso.</p>
        </div>
    @else
        @foreach($grupos as $grupo)
            @include('schedules._tabla-docente', ['dias' => $dias, 'diasLabel' => $diasLabel, 'filas' => $grupo['filas'], 'tabla' => $grupo['tabla'], 'titulo' => $grupo['titulo']])
        @endforeach
    @endif
@endsection