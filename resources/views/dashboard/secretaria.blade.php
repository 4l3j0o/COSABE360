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
        $pagosEspera = $pagosEnEspera ?? 0;
        $docsPend = $documentosPendientes ?? 0;
    @endphp

    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Panel de Secretaría</h1>
                <p class="text-sm text-muted-foreground mt-1">Gestión de estudiantes, inscripciones, matrículas y pagos.</p>
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
        <x-ui.stat-card label="Pagos por cobrar" :value="($pagosPend + $pagosEspera) ? ($pagosPend + $pagosEspera) : '--'" color="warning" />
        <x-ui.stat-card label="Documentos pendientes" :value="$docsPend" color="error" />
    </div>

    @if(($pagosPend + $pagosEspera) > 0 || $docsPend > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        @if(($pagosPend + $pagosEspera) > 0)
        <a href="{{ route('students.index') }}" class="flex items-center gap-4 bg-card border border-border rounded-xl p-4">
            <span class="w-10 h-10 rounded-lg bg-warning flex items-center justify-center text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="flex-1">
                <p class="font-semibold text-foreground">{{ $pagosPend + $pagosEspera }} pago(s) por cobrar ({{ $pagosEspera }} en espera)</p>
                <p class="text-xs text-muted-foreground">Gestionar mensualidades y deudas por estudiante</p>
            </div>
            <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @endif
        @if($docsPend > 0)
        <a href="{{ route('students.index') }}" class="flex items-center gap-4 bg-card border border-border rounded-xl p-4">
            <span class="w-10 h-10 rounded-lg bg-error flex items-center justify-center text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <div class="flex-1">
                <p class="font-semibold text-foreground">{{ $docsPend }} documento(s) pendiente(s)</p>
                <p class="text-xs text-muted-foreground">Revisar documentación de estudiantes</p>
            </div>
            <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @endif
    </div>
    @endif

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('students.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Estudiantes</span>
                    <p class="text-xs text-muted-foreground">Inscribir, matricular y gestionar pagos</p>
                </div>
            </a>
            <a href="{{ route('asistencias.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v2h4a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm14 14H6m12-2H6m6-12v6m0 0l-2-2m2 2l2-2"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Cursos y Asistencias</span>
                    <p class="text-xs text-muted-foreground">Registrar y consultar asistencia</p>
                </div>
            </a>
            <a href="{{ route('rude.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">RUDE</span>
                    <p class="text-xs text-muted-foreground">Formulario y exportación RUDE</p>
                </div>
            </a>
            <a href="{{ route('evaluation-reports.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Reportes de Evaluación</span>
                    <p class="text-xs text-muted-foreground">Consultar notas por curso y trimestre</p>
                </div>
            </a>
            <a href="{{ route('reportes.central.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
                <div>
                    <span class="font-medium text-foreground">Central de Reportes</span>
                    <p class="text-xs text-muted-foreground">Inscripciones, matrículas y pagos</p>
                </div>
            </a>
        </div>
    </div>
@endsection
