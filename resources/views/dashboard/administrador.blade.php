@extends('layouts.app')
@section('titulo', 'Panel Administrador')
@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Panel de Administración</h1>
                <p class="text-sm text-muted-foreground mt-1">Control total del sistema: usuarios, estudiantes, docentes y reportes.</p>
            </div>
            <div class="flex items-center gap-2 border border-border rounded-lg px-4 py-2">
                <span class="text-sm font-medium text-foreground">
                    {{ isset($periodoActivo) && $periodoActivo ? 'Gestión ' . $periodoActivo->nombre : 'Sin período activo' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card label="Total Estudiantes" :value="$totalEstudiantes ?? '--'" color="primary" />
        <x-ui.stat-card label="Total Docentes" :value="$totalDocentes ?? '--'" color="success" />
        <x-ui.stat-card label="Total Usuarios" :value="$totalUsuarios ?? '--'" color="info" />
        <x-ui.stat-card label="Matriculados" :value="$totalMatriculados ?? '--'" color="warning" />
        <x-ui.stat-card label="Cursos" :value="$totalCursos ?? '--'" color="primary" />
        <x-ui.stat-card label="Materias" :value="$totalMaterias ?? '--'" color="success" />
        <x-ui.stat-card label="Pagos por cobrar" :value="(($pagosPendientes ?? 0) + ($pagosEnEspera ?? 0)) ? (($pagosPendientes ?? 0) + ($pagosEnEspera ?? 0)) : '--'" color="warning" />
        <x-ui.stat-card label="Documentos pendientes" :value="$documentosPendientes ?? '--'" color="error" />
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos Rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('students.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Estudiantes</span>
                    <p class="text-muted-foreground text-xs">Lista Estudiantes</p>
                </div>
            </a>
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Usuarios</span>
                    <p class="text-muted-foreground text-xs">Lista Usuarios</p>
                </div>
            </a>
            <a href="{{ route('teachers.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l8-4-8-4-8 4 8 4zm0 4l8-4M4 10l8 4 8-4M12 14v7"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Docentes</span>
                    <p class="text-muted-foreground text-xs">Lista Docentes</p>
                </div>
            </a>
            <a href="{{ route('reportes.central.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Central de Reportes</span>
                    <p class="text-muted-foreground text-xs">Académicos y administrativos</p>
                </div>
            </a>
            <a href="{{ route('tax-configuration.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border text-sm">
                <span class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-primary shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <div>
                    <span class="text-foreground font-medium">Configuración</span>
                    <p class="text-muted-foreground text-xs">Sistema y parámetros</p>
                </div>
            </a>
        </div>
    </div>
@endsection
