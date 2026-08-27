@extends('layouts.app')

@section('titulo', 'Editar docente')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-foreground">Editar docente</h1>
            <p class="text-sm text-muted-foreground mt-1">Código: <span class="font-mono font-semibold text-primary">{{ $teacher->codigo }}</span> · Estado: <span class="{{ $teacher->estaActivo() ? 'text-success' : 'text-error' }} font-medium">{{ $teacher->estado }}</span></p>
        </div>

        <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="bg-card border border-border rounded-xl p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nombres" class="block text-sm font-medium text-foreground mb-1.5">Nombres *</label>
                    <input id="nombres" name="nombres" type="text" value="{{ old('nombres', $teacher->nombres) }}" required
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('nombres') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="apellidos" class="block text-sm font-medium text-foreground mb-1.5">Apellidos *</label>
                    <input id="apellidos" name="apellidos" type="text" value="{{ old('apellidos', $teacher->apellidos) }}" required
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('apellidos') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="especialidad" class="block text-sm font-medium text-foreground mb-1.5">Especialidad</label>
                <input id="especialidad" name="especialidad" type="text" value="{{ old('especialidad', $teacher->especialidad) }}"
                       class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                @error('especialidad') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="tipo_documento" class="block text-sm font-medium text-foreground mb-1.5">Tipo doc. *</label>
                    <select id="tipo_documento" name="tipo_documento" required
                            class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        <option value="CI" @selected(old('tipo_documento', $teacher->tipo_documento)=='CI')>CI</option>
                        <option value="PASAPORTE" @selected(old('tipo_documento', $teacher->tipo_documento)=='PASAPORTE')>PASAPORTE</option>
                        <option value="OTRO" @selected(old('tipo_documento', $teacher->tipo_documento)=='OTRO')>OTRO</option>
                    </select>
                    @error('tipo_documento') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="numero_documento" class="block text-sm font-medium text-foreground mb-1.5">Nro documento *</label>
                    <input id="numero_documento" name="numero_documento" type="text" value="{{ old('numero_documento', $teacher->numero_documento) }}" required
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('numero_documento') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="complemento" class="block text-sm font-medium text-foreground mb-1.5">Complemento</label>
                    <input id="complemento" name="complemento" type="text" value="{{ old('complemento', $teacher->complemento) }}"
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('complemento') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="telefono" class="block text-sm font-medium text-foreground mb-1.5">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $teacher->telefono) }}"
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('telefono') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="correo" class="block text-sm font-medium text-foreground mb-1.5">Correo</label>
                    <input id="correo" name="correo" type="email" value="{{ old('correo', $teacher->correo) }}"
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('correo') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('teachers.index') }}" class="text-center px-5 py-2.5 text-sm font-medium border border-border rounded-lg hover:bg-accent transition-colors">Cancelar</a>
                <button type="submit" class="bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer">Actualizar docente</button>
            </div>
        </form>
    </div>
@endsection
