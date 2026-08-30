@extends('layouts.app')
@section('titulo', 'Ver Comunicado')

@section('sidebar')
    @php
        $sidebarComunicado = match (auth()->user()->rol?->nombre) {
            'Docente' => 'dashboard.partials._sidebar-docente',
            'Estudiante', 'Padre/Tutor' => 'dashboard.partials._sidebar-estudiante',
            default => 'dashboard.partials._sidebar-admin',
        };
    @endphp
    @include($sidebarComunicado)
@endsection

@section('contenido')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('comunicados.bandeja') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a mis comunicados
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">{{ $announcement->titulo }}</h1>
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $leido ? 'Leído' : 'Nuevo' }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $announcement->author?->nombre }} {{ $announcement->author?->apellido }}</span>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-6">
            <div class="whitespace-pre-wrap text-sm text-foreground leading-relaxed">{{ $announcement->contenido }}</div>
        </div>

        @if(! $leido)
            <div class="flex items-center justify-end mt-4">
                <form method="POST" action="{{ route('comunicados.bandeja.leido', $announcement) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Marcar como leído
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection