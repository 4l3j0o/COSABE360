@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Usuarios</h1>
            <p class="text-sm text-muted-foreground">Gestión de usuarios del sistema</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    @if($users->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-muted-foreground text-sm">No hay usuarios registrados</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Nombre</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Correo</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Teléfono</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Rol</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $user->nombre }} {{ $user->apellido }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $user->telefono ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $rolColores = [
                                            'Administrador' => 'bg-primary/10 text-primary',
                                            'Director'      => 'bg-info/10 text-info',
                                            'Secretaría'    => 'bg-warning/10 text-warning',
                                            'Docente'       => 'bg-success/10 text-success',
                                            'Padre/Tutor'   => 'bg-neutral-100 text-neutral-600',
                                            'Estudiante'    => 'bg-info/10 text-info',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $rolColores[$user->rol?->nombre] ?? 'bg-muted text-muted-foreground' }}">
                                        {{ $user->rol?->nombre ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $estados = [
                                            'ACTIVO'   => 'bg-success/10 text-success',
                                            'INACTIVO' => 'bg-warning/10 text-warning',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $estados[$user->estado] ?? 'bg-muted text-muted-foreground' }}">
                                        {{ $user->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.edit', $user) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @unless ($user->is(auth()->user()))
                                            <form method="POST" action="{{ route('users.toggle', $user) }}" onsubmit="return confirm('¿Seguro que deseas {{ $user->estaActivo() ? 'desactivar' : 'activar' }} a este usuario?')" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="{{ $user->estaActivo() ? 'Desactivar' : 'Activar' }}">
                                                    @if($user->estaActivo())
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Seguro que deseas eliminar a este usuario? Esta acción no se puede deshacer.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-error/10 transition-colors text-muted-foreground hover:text-error cursor-pointer" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
             <div class="px-4 py-3 border-t border-border">
                {{ $users->links('pagina.custom') }}
            </div>
        </div>
    @endif
@endsection
