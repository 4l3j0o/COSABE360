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
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-xl p-4 mb-5 text-sm">{{ session('success') }}</div>
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
@endsection
