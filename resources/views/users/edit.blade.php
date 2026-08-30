@extends('layouts.app')
@section('titulo', 'Editar usuario')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
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

            <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre <span class="text-error">*</span></label>
                        <input name="nombre" type="text" value="{{ old('nombre', $user->nombre) }}" required autofocus
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellido <span class="text-error">*</span></label>
                        <input name="apellido" type="text" value="{{ old('apellido', $user->apellido) }}" required
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
                        <input name="email" type="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Contraseña <span class="text-muted-foreground font-normal">(vacío = mantener)</span></label>
                        <input name="password" type="password"
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
                                <option value="{{ $rol->id }}" @selected(old('role_id', $user->role_id) == $rol->id)>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                        <input name="telefono" type="tel" value="{{ old('telefono', $user->telefono) }}"
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('telefono') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-lg bg-muted/30 border border-border text-sm">
                <span class="text-muted-foreground">Estado:</span>
                @php
                    $estadoColor = $user->estaActivo() ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning';
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $estadoColor }}">{{ mb_strtolower($user->estado) }}</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('users.index') }}"
                   class="px-5 py-2.5 text-sm font-medium border border-border rounded-lg hover:bg-accent transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-5 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Actualizar usuario
                </button>
            </div>
        </form>
    </div>
@endsection
