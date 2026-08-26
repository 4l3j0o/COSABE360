@extends('layouts.app')

@section('titulo', 'Panel Administrador')

@section('sidebar')
    @include('dashboards.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Total Estudiantes</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Total Docentes</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Total Usuarios</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Año Académico</p>
            <p class="text-2xl font-bold text-primary mt-1">--</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Accesos Rápidos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('students.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary">Estudiantes</span>
                <span class="text-muted-foreground">CRUD completo</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary">Docentes</span>
                <span class="text-muted-foreground">Gestión de personal</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-4 rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors text-sm">
                <span class="text-primary">Configuración</span>
                <span class="text-muted-foreground">Sistema y parámetros</span>
            </a>
        </div>
    </div>
@endsection
