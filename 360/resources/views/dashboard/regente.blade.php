@extends('layouts.app')

@section('titulo', 'Panel Regente')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $perActivo = $periodoActivo ?? null;
        $totEstudiantes = $totalEstudiantes ?? 0;
        $totCursos = $totalCursos ?? 0;
        $totMatriculados = $totalMatriculados ?? 0;
        $totComunicados = $totalComunicados ?? 0;
    @endphp

    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Panel de Regente</h1>
                <p class="text-sm text-muted-foreground mt-1">Gestión de listas de asistencia, licencias y comunicados.</p>
            </div>
            <div class="flex items-center gap-2 border border-border rounded-lg px-4 py-2">
                <span class="text-sm font-medium text-foreground">
                    {{ $perActivo ? 'Gestión ' . $perActivo->nombre : 'Sin período activo' }}
                </span>
            </div>
        </div>
    </div>

    @include('dashboard.partials._splash-comunicados')

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Total Estudiantes" :value="$totEstudiantes" color="primary" />
        <x-ui.stat-card label="Estudiantes matriculados" :value="$totMatriculados" color="success" />
        <x-ui.stat-card label="Cursos / paralelos" :value="$totCursos" color="warning" />
        <x-ui.stat-card label="Comunicados" :value="$totComunicados" color="primary" />
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('regente.cursos') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm hover:border-primary/40 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3L22 4m-6 0H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V12"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Listas de Asistencia</span>
                    <p class="text-xs text-muted-foreground">Consolidado por curso, licencias y correcciones</p>
                </div>
            </a>
            <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm hover:border-primary/40 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Comunicados</span>
                    <p class="text-xs text-muted-foreground">Avisos de curso e institucionales</p>
                </div>
            </a>
            <a href="{{ route('reportes.central.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm hover:border-primary/40 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Central de Reportes</span>
                    <p class="text-xs text-muted-foreground">Asistencia, matrículas y evaluaciones</p>
                </div>
            </a>
        </div>
    </div>
@endsection
