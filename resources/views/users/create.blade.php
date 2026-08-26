@extends('layouts.app')

@section('titulo', 'Nuevo usuario')

@section('sidebar')
    @include('dashboards.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-foreground">Nuevo usuario</h1>
            <p class="text-sm text-muted-foreground mt-1">Completa los datos para crear un usuario. El correo debe ser único.</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}"
              class="bg-card border border-border rounded-xl p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-foreground mb-1.5">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" required autofocus
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('nombre') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="apellido" class="block text-sm font-medium text-foreground mb-1.5">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="{{ old('apellido') }}" required
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('apellido') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="correo" class="block text-sm font-medium text-foreground mb-1.5">Correo electrónico</label>
                <input id="correo" name="correo" type="email" value="{{ old('correo') }}" required
                       class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                @error('correo') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-medium text-foreground mb-1.5">Contraseña</label>
                    <input id="password" name="password" type="password" required
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('password') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="rol_id" class="block text-sm font-medium text-foreground mb-1.5">Rol</label>
                    <select id="rol_id" name="rol_id"
                            class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('rol_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-foreground mb-1.5">Teléfono <span class="font-normal text-muted-foreground">(opcional)</span></label>
                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono') }}"
                       class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                @error('telefono') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('users.index') }}"
                   class="text-center px-5 py-2.5 text-sm font-medium border border-border rounded-lg hover:bg-accent transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer">
                    Guardar usuario
                </button>
            </div>
        </form>
    </div>
@endsection
