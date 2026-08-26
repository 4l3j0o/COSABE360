@extends('layouts.app')

@section('titulo', 'Editar usuario')

@section('sidebar')
    @include('dashboards.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-foreground">Editar usuario</h1>
            <p class="text-sm text-muted-foreground mt-1">Actualiza la información. Deja la contraseña vacía para mantenerla.</p>
        </div>

        <form method="POST" action="{{ route('users.update', $user) }}"
              class="bg-card border border-border rounded-xl p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-foreground mb-1.5">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $user->nombre) }}" required autofocus
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('nombre') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="apellido" class="block text-sm font-medium text-foreground mb-1.5">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="{{ old('apellido', $user->apellido) }}" required
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('apellido') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="correo" class="block text-sm font-medium text-foreground mb-1.5">Correo electrónico</label>
                <input id="correo" name="correo" type="email" value="{{ old('correo', $user->correo) }}" required
                       class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                @error('correo') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                <p class="text-xs text-muted-foreground mt-1">Validación: correo no duplicado.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-medium text-foreground mb-1.5">Contraseña <span class="font-normal text-muted-foreground">(vacío = mantener)</span></label>
                    <input id="password" name="password" type="password"
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('password') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="rol_id" class="block text-sm font-medium text-foreground mb-1.5">Rol</label>
                    <select id="rol_id" name="rol_id"
                            class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" @selected(old('rol_id', $user->rol_id) == $rol->id)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('rol_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-foreground mb-1.5">Teléfono <span class="font-normal text-muted-foreground">(opcional)</span></label>
                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $user->telefono) }}"
                       class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                @error('telefono') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/30 border border-border text-sm">
                <span class="font-medium text-foreground">Estado actual:</span>
                <span class="{{ $user->estaActivo() ? 'text-success' : 'text-error' }} font-medium">{{ $user->estado }}</span>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('users.index') }}"
                   class="text-center px-5 py-2.5 text-sm font-medium border border-border rounded-lg hover:bg-accent transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer">
                    Actualizar usuario
                </button>
            </div>
        </form>
    </div>
@endsection
