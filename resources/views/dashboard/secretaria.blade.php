@extends('layouts.app')

@section('titulo', 'Panel Secretaría')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $perActivo = $periodoActivo ?? null;
        $totEstudiantes = $totalEstudiantes ?? 0;
        $totMatriculados = $totalMatriculados ?? 0;
        $pagosPend = $pagosPendientes ?? 0;
        $docsPend = $documentosPendientes ?? 0;
    @endphp
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Panel de Secretaría</h1>
            <p class="text-sm text-muted-foreground">
                Gestión de estudiantes, inscripciones, matrículas y pagos
                @if($perActivo)
                    — Período {{ $perActivo->nombre }}
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Estudiantes</p>
            <p class="text-2xl font-bold text-foreground mt-1">{{ $totEstudiantes }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Matriculados</p>
            <p class="text-2xl font-bold text-success mt-1">{{ $totMatriculados }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Pagos pendientes</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ $pagosPend }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Documentos pendientes</p>
            <p class="text-2xl font-bold text-error mt-1">{{ $docsPend }}</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('students.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                <div>
                    <span class="font-medium text-foreground">Estudiantes</span>
                    <p class="text-xs text-muted-foreground">Inscribir, matricular y gestionar pagos</p>
                </div>
            </a>
            <a href="{{ route('asistencias.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v2h4a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm14 14H6m12-2H6m6-12v6m0 0l-2-2m2 2l2-2"/></svg>
                <div>
                    <span class="font-medium text-foreground">Cursos y Asistencias</span>
                    <p class="text-xs text-muted-foreground">Registrar y consultar asistencia</p>
                </div>
            </a>
            <a href="{{ route('rude.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div>
                    <span class="font-medium text-foreground">RUDE</span>
                    <p class="text-xs text-muted-foreground">Formulario y exportación RUDE</p>
                </div>
            </a>
            <a href="{{ route('notas-finales.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <div>
                    <span class="font-medium text-foreground">Notas Finales</span>
                    <p class="text-xs text-muted-foreground">Consultar notas por estudiante</p>
                </div>
            </a>
            <a href="{{ route('reportes.academicos.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div>
                    <span class="font-medium text-foreground">Reportes Académicos</span>
                    <p class="text-xs text-muted-foreground">Calificaciones y promedios</p>
                </div>
            </a>
            <a href="{{ route('reportes.administrativos.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-muted transition-colors text-sm">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <div>
                    <span class="font-medium text-foreground">Reportes Administrativos</span>
                    <p class="text-xs text-muted-foreground">Inscripciones, matrículas y pagos</p>
                </div>
            </a>
        </div>
    </div>
@endsection
