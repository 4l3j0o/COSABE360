@extends('layouts.app')
@section('titulo', 'Ver Comunicado')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('comunicados.bandeja') }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a mis comunicados
            </a>
        </div>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <div class="h-1.5 {{ $leido ? 'bg-muted' : 'bg-primary' }}"></div>

            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $leido ? 'bg-muted text-muted-foreground' : 'bg-primary/10 text-primary' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $leido ? 'bg-muted-foreground' : 'bg-primary' }}"></span>
                        {{ $leido ? 'Leído' : 'Nuevo' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-foreground leading-tight">{{ $announcement->titulo }}</h1>

                <div class="flex items-center gap-3 mt-5 mb-7 pb-6 border-b border-border">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $leido ? 'bg-muted text-muted-foreground' : 'bg-primary/15 text-primary' }} text-sm font-bold">
                        {{ substr($announcement->author?->nombre ?? 'U', 0, 1) }}{{ substr($announcement->author?->apellido ?? '', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-foreground">{{ trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Administración' }}</p>
                        <p class="text-xs text-muted-foreground">{{ ucfirst(mb_strtolower($announcement->author?->rol?->nombre ?? 'Dirección')) }} · U.E. San Bernardo</p>
                    </div>
                </div>

                <div class="whitespace-pre-wrap text-[15px] text-foreground leading-relaxed">{{ $announcement->contenido }}</div>
            </div>
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
