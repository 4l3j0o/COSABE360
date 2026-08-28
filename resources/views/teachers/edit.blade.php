@extends('layouts.app')
@section('titulo', 'Editar Docente')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="fixed inset-0 z-20 flex items-start justify-center pt-4 pb-8 px-4 overflow-y-auto" style="margin: -1.5rem; padding: 1.5rem;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="window.location='{{ route('teachers.index') }}'"></div>

        <div class="relative w-full max-w-3xl bg-card border border-border rounded-2xl shadow-xl z-30">
            <div class="sticky top-0 flex items-center justify-between px-6 py-4 border-b border-border rounded-t-2xl bg-card">
                <div>
                    <h2 class="text-base font-bold text-foreground">Editar: {{ $teacher->apellidos }}, {{ $teacher->nombres }}</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Código: <span class="font-mono font-semibold text-primary">{{ $teacher->codigo }}</span> · Estado: <span class="{{ $teacher->estaActivo() ? 'text-success' : 'text-error' }} font-medium">{{ $teacher->estado }}</span></p>
                </div>
                <a href="{{ route('teachers.index') }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="p-6 space-y-6">
                @csrf @method('PUT')

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $teacher->codigo) }}" readonly class="w-full px-3 py-2.5 border border-input rounded-lg bg-muted/50 text-foreground text-sm">
                            @error('codigo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                            <input type="text" name="nombres" value="{{ old('nombres', $teacher->nombres) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                            <input type="text" name="apellidos" value="{{ old('apellidos', $teacher->apellidos) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Especialidad</label>
                            <input type="text" name="especialidad" value="{{ old('especialidad', $teacher->especialidad) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('especialidad') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                            <select name="estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="ACTIVO" {{ old('estado', $teacher->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                <option value="INACTIVO" {{ old('estado', $teacher->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                            <select name="tipo_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="CI" {{ old('tipo_documento', $teacher->tipo_documento) === 'CI' ? 'selected' : '' }}>Cédula de Identidad</option>
                                <option value="PASAPORTE" {{ old('tipo_documento', $teacher->tipo_documento) === 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                <option value="OTRO" {{ old('tipo_documento', $teacher->tipo_documento) === 'OTRO' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento', $teacher->numero_documento) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                            <input type="text" name="complemento" value="{{ old('complemento', $teacher->complemento) }}" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('complemento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $teacher->telefono) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('telefono') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo Electrónico</label>
                            <input type="email" name="correo" value="{{ old('correo', $teacher->correo) }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('correo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 flex items-center justify-end gap-3 pt-4 border-t border-border bg-card rounded-b-2xl -mx-6 -mb-6 px-6 pb-6">
                    <a href="{{ route('teachers.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</a>
                    <button type="submit" class="bg-primary text-primary-foreground px-5 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection
