@extends('layouts.app')
@section('titulo', 'Roles y permisos')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Roles y permisos</h1>
            <p class="text-sm text-muted-foreground">Administre qué puede hacer cada rol dentro de 360.</p>
        </div>
        <button type="button" onclick="openModal('modal-create-role')" class="inline-flex items-center justify-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo rol
        </button>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-xl p-4 mb-5 text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-5 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-left text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Rol</th>
                        <th class="px-4 py-3 font-semibold">Descripción</th>
                        <th class="px-4 py-3 font-semibold">Permisos</th>
                        <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $rol)
                        <tr class="border-t border-border hover:bg-muted/20">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $rol->nombre }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $rol->descripcion ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-primary/10 text-primary px-2.5 py-1 text-xs font-semibold">
                                    {{ $rol->nombre === 'Administrador' ? 'Todos' : $rol->permissions_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('roles.edit', $rol) }}" class="inline-flex items-center gap-1.5 text-primary hover:underline text-sm font-medium">
                                    Ver / editar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-create-role" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="absolute inset-0 bg-black/50" onclick="closeModal('modal-create-role')"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-card border border-border rounded-2xl shadow-2xl p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-foreground">Nuevo rol</h2>
                        <p class="text-sm text-muted-foreground">Cree el rol y luego asígnele permisos.</p>
                    </div>
                    <button type="button" onclick="closeModal('modal-create-role')" class="text-muted-foreground hover:text-foreground" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('roles.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="rol-nombre" class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Nombre del rol</label>
                        <input id="rol-nombre" type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="50"
                               class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="rol-descripcion" class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Descripción</label>
                        <textarea id="rol-descripcion" name="descripcion" rows="3" maxlength="255"
                                  class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('descripcion') }}</textarea>
                        @error('descripcion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="closeModal('modal-create-role')" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</button>
                        <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Crear rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->has('nombre') || $errors->has('descripcion'))
                openModal('modal-create-role');
            @endif
        });
    </script>
@endsection
