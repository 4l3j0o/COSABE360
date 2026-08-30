@extends('layouts.app')
@section('titulo', 'Mis Comunicados')

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
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Mis Comunicados</h1>
        <p class="text-sm text-muted-foreground">Comunicados publicados para ti</p>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif

    @if($announcements->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-muted-foreground text-sm">No tienes comunicados pendientes.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            @foreach($announcements as $announcement)
                <div class="flex items-start justify-between gap-4 px-4 py-4 border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                    <div class="min-w-0 flex items-start gap-3">
                        <div class="shrink-0 mt-0.5 flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('comunicados.bandeja.show', $announcement) }}" class="font-medium text-foreground hover:text-primary transition-colors inline-flex items-center gap-2">
                                {{ $announcement->titulo }}
                                @if($announcement->es_nuevo)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary text-primary-foreground">Nuevo</span>
                                @endif
                            </a>
                            <p class="text-xs text-muted-foreground mt-1">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }} · {{ $announcement->author?->nombre }} {{ $announcement->author?->apellido }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
            @if($announcements->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection