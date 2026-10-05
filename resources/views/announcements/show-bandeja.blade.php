@extends('layouts.app')
@section('titulo', $announcement->titulo)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('announcements.index', ['tab' => 'recibidos']) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver a Comunicados
    </a>

    <div class="bg-card border rounded-xl p-6 sm:p-8 shadow-sm {{ $announcement->es_nuevo ? 'border-primary/40' : 'border-border' }}">
        <div class="flex items-start justify-between gap-3">
            <h1 class="text-xl font-bold text-foreground leading-snug">{{ $announcement->titulo }}</h1>
            @if($announcement->es_nuevo)
                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary text-primary-foreground">
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span> Nuevo
                </span>
            @endif
        </div>

        <div class="flex items-center gap-3 mt-4 mb-6 pb-5 border-b border-border">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/15 text-primary text-sm font-bold">
                {{ substr($announcement->author?->nombre ?? 'U', 0, 1) }}{{ substr($announcement->author?->apellido ?? '', 0, 1) }}
            </div>
            <div>
                <p class="text-sm font-medium text-foreground">{{ trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Administración' }}</p>
                <p class="text-xs text-muted-foreground">{{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }} · CSB 360</p>
            </div>
        </div>

        <div class="whitespace-pre-wrap text-[15px] text-foreground leading-relaxed">{!! $announcement->contenido !!}</div>

        @if($announcement->es_nuevo)
            <form method="POST" action="{{ route('comunicados.bandeja.leido', $announcement) }}" class="mt-6 pt-5 border-t border-border">
                @csrf @method('PATCH')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Marcar como leído
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
