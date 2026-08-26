@extends('layouts.app')

@section('titulo', 'Acceso Denegado')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-foreground mb-2">Acceso Denegado</h1>
        <p class="text-sm text-muted-foreground mb-6 text-center max-w-md">
            No tienes permisos para acceder a esta sección. Si crees que esto es un error, contacta al administrador.
        </p>
        <a href="{{ route('dashboard') }}" class="bg-primary text-primary-foreground font-semibold py-2.5 px-6 rounded-lg hover:opacity-90 transition-all text-sm">
            Volver al Dashboard
        </a>
    </div>
@endsection
