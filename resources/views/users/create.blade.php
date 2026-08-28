@extends('layouts.app')
@section('titulo', 'Nuevo usuario')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
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

            <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre <span class="text-error">*</span></label>
                        <input name="nombre" type="text" value="{{ old('nombre') }}" required autofocus
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellido <span class="text-error">*</span></label>
                        <input name="apellido" type="text" value="{{ old('apellido') }}" required
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('apellido') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Cuenta de Acceso</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo electrónico <span class="text-error">*</span></label>
                        <input name="email" type="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Contraseña <span class="text-error">*</span></label>
                        <input name="password" type="password" required
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('password') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Rol y Contacto</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Rol <span class="text-error">*</span></label>
                        <select name="role_id"
                                class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(old('role_id') == $rol->id)>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                        <input name="telefono" type="tel" value="{{ old('telefono') }}"
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('telefono') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('users.index') }}"
                   class="px-5 py-2.5 text-sm font-medium border border-border rounded-lg hover:bg-accent transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-5 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar usuario
                </button>
            </div>
        </form>
    </div>
@endsection
