@extends('layouts.app')
@section('titulo', $announcement->titulo)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
@php
    $autor = trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Administración';
    $iniciales = mb_strtoupper(mb_substr($announcement->author?->nombre ?? 'A', 0, 1) . mb_substr($announcement->author?->apellido ?? '', 0, 1));
    $rolAutor = $announcement->author?->rol?->nombre;
    $texto = nl2br(e($announcement->contenido));
    $texto = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $texto);
@endphp
<div class="w-full">
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('announcements.index', ['tab' => 'recibidos']), 'texto' => 'Volver a Recibidos'])
    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-3">
                <h1 class="text-xl font-bold text-foreground leading-snug break-words">{{ $announcement->titulo }}</h1>
                <div class="shrink-0 flex flex-col items-end gap-1.5">
                    @if($announcement->destacado)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-warning/15 text-warning">Importante</span>
                    @endif
                    @if($announcement->es_nuevo)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary text-primary-foreground">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span> Nuevo
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 mt-4 mb-6 pb-5 border-b border-border">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/15 text-primary text-sm font-bold">
                    {{ $iniciales ?: 'A' }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-foreground">{{ $autor }} <span class="font-normal text-muted-foreground">para ti</span></p>
                    <p class="text-xs text-muted-foreground">
                        {{ $announcement->fecha_publicacion?->format('d/m/Y H:i') }}
                        @if($rolAutor) · {{ $rolAutor }} @endif
                    </p>
                </div>
            </div>

            <div class="whitespace-pre-wrap break-words text-[15px] text-foreground leading-relaxed">{!! $texto !!}</div>

            @if($announcement->imagen_path)
                @include('announcements._adjunto-imagen', ['path' => $announcement->imagen_path])
            @endif
        </div>
    </div>
</div>
@endsection
