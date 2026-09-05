@extends('layouts.app')
@section('titulo', 'Editar Nivel')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('levels.index') }}" class="text-sm text-muted-foreground hover:text-foreground">← Volver a niveles</a>
            <h1 class="text-xl font-bold text-foreground mt-1">Editar Nivel: {{ $level->nombre }}</h1>
        </div>

        <form method="POST" action="{{ route('levels.update', $level) }}" class="bg-card border border-border rounded-xl p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre <span class="text-error">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre', $level->nombre) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $level->descripcion) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                @error('descripcion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Orden <span class="text-error">*</span></label>
                    <input type="number" name="orden" value="{{ old('orden', $level->orden) }}" required min="1" max="255" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @error('orden') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                    <select name="estado" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="ACTIVO" {{ old('estado', $level->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVO" {{ old('estado', $level->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('levels.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm">Cancelar</a>
                <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">Actualizar</button>
            </div>
        </form>
    </div>
@endsection
