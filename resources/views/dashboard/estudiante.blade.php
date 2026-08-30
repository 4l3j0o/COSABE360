@extends('layouts.app')

@section('titulo', 'Panel Estudiante')

@section('sidebar')
    @include('dashboard.partials._sidebar-estudiante')
@endsection

@section('contenido')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Promedio General</p>
            <p class="text-2xl font-bold text-primary mt-1">--</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Asistencia</p>
            <p class="text-2xl font-bold text-foreground mt-1">--%</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-5">
            <p class="text-sm text-muted-foreground">Materias</p>
            <p class="text-2xl font-bold text-foreground mt-1">--</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Mi Información</h3>
        <p class="text-sm text-muted-foreground">Bienvenido al panel del Estudiante. Aquí podrá consultar sus calificaciones, horario y asistencia.</p>
    </div>
@endsection
