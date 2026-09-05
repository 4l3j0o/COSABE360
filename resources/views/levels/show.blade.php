@extends('layouts.app')
@section('titulo', $level->nombre)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('levels.index') }}" class="text-sm text-muted-foreground hover:text-foreground">← Volver a niveles</a>
        <div class="bg-card border border-border rounded-xl p-6 mt-4">
            <h1 class="text-xl font-bold text-foreground">{{ $level->nombre }}</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ $level->descripcion ?: 'Sin descripción' }}</p>
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 text-sm">
                <div>
                    <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold">Orden</dt>
                    <dd class="text-foreground font-medium mt-1">{{ $level->orden }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold">Estado</dt>
                    <dd class="mt-1"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $level->estado === 'ACTIVO' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">{{ $level->estado }}</span></dd>
                </div>
                <div>
                    <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold">Grados</dt>
                    <dd class="text-foreground mt-1">{{ $level->grades->count() }} asociados</dd>
                </div>
            </dl>
            <div class="flex gap-2 mt-6">
                <a href="{{ route('levels.edit', $level) }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">Editar</a>
                <a href="{{ route('levels.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm">Volver</a>
            </div>
        </div>
    </div>
@endsection
