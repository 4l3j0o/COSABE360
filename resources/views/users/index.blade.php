@extends('layouts.app')

@section('titulo', 'Gestión de usuarios')

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Gestión de usuarios</h1>
            <p class="text-sm text-muted-foreground mt-1">Administra cuentas, roles y estados</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center justify-center bg-primary text-primary-foreground font-semibold py-2.5 px-5 rounded-lg hover:opacity-90 active:scale-[0.98] transition-all text-sm shadow-sm">
            + Nuevo usuario
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-muted/50 text-muted-foreground border-b border-border">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Correo</th>
                        <th class="px-4 py-3 font-medium">Teléfono</th>
                        <th class="px-4 py-3 font-medium">Rol</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $user->nombre }} {{ $user->apellido }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $user->telefono ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->esAdmin() ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-muted text-muted-foreground border border-border' }}">
                                    {{ $user->rol?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->estaActivo() ? 'text-success' : 'text-error' }}">
                                    <span class="size-2 rounded-full {{ $user->estaActivo() ? 'bg-success' : 'bg-error' }}"></span>
                                    {{ ucfirst(strtolower($user->estado)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="inline-flex px-3 py-1.5 border border-border bg-card hover:bg-accent hover:text-accent-foreground rounded-lg text-xs font-medium transition-colors">
                                        Editar
                                    </a>
                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('users.toggle', $user) }}" onsubmit="return confirm('¿Seguro que deseas {{ $user->estaActivo() ? 'desactivar' : 'activar' }} a este usuario?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors cursor-pointer {{ $user->estaActivo() ? 'border-error/30 text-error hover:bg-error/10' : 'border-success/30 text-success hover:bg-success/10' }}">
                                                {{ $user->estaActivo() ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
