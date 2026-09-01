@extends('layouts.app')

@section('titulo', 'Panel Padre/Tutor')

@section('sidebar')
    @include('dashboard.partials._sidebar-tutor')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Panel de Padre/Tutor</h1>
        <p class="text-sm text-muted-foreground mt-1">Estudiantes de los que es responsable como tutor</p>
    </div>

    @if(isset($hijos) && $hijos->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($hijos as $hijo)
                <div class="bg-card border border-border rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 rounded-full bg-muted text-primary flex items-center justify-center font-semibold text-sm shrink-0">
                            {{ substr($hijo->nombres, 0, 1) }}{{ substr($hijo->apellidos, 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <h4 class="font-semibold text-foreground text-sm truncate">{{ $hijo->nombres }} {{ $hijo->apellidos }}</h4>
                            <p class="text-xs text-muted-foreground font-mono">{{ $hijo->codigo }}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('monthly-payments.estudiante', $hijo) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-white bg-primary hover:opacity-90 font-medium shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Pagos y Facturas
                        </a>
                        <a href="{{ route('calificaciones.index', ['student_id' => $hijo->id]) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-foreground border border-border">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Calificaciones
                        </a>
                        <a href="{{ route('notas-finales.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-foreground border border-border">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Notas Finales
                        </a>
                        <a href="{{ route('historial.index', ['student_id' => $hijo->id]) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-foreground border border-border">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Historial Académico
                        </a>
                        <a href="{{ route('asistencias.historial') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-foreground border border-border">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Asistencia
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron estudiantes a su cargo.</p>
        </div>
    @endif
@endsection
