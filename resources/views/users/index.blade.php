@extends('layouts.app')

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Gestión de usuarios</h1>

        <a href="{{ route('users.create') }}"
           class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] rounded-sm text-sm font-medium hover:opacity-90 transition">
            + Nuevo usuario
        </a>
    </div>

    <div class="bg-white dark:bg-[#161615] border border-[#19140035] dark:border-[#3E3E3A] rounded-sm overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#706f6c] dark:text-[#A1A09A] border-b border-[#19140035] dark:border-[#3E3E3A]">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Correo</th>
                    <th class="px-4 py-3">Teléfono</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b border-[#19140015] dark:border-[#3E3E3A]/50 last:border-0 hover:bg-[#FDFDFC] dark:hover:bg-[#0a0a0a] transition">
                        <td class="px-4 py-3 font-medium">{{ $user->nombre }} {{ $user->apellido }}</td>
                        <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">{{ $user->telefono ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $user->esAdmin() ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                                {{ $user->rol?->nombre }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs {{ $user->estaActivo() ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                <span class="size-1.5 rounded-full {{ $user->estaActivo() ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ ucfirst(strtolower($user->estado)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="px-3 py-1.5 border border-[#19140035] dark:border-[#3E3E3A] hover:border-[#1915014a] dark:hover:border-[#62605b] rounded-sm text-xs transition">
                                    Editar
                                </a>
                                @unless ($user->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.toggle', $user) }}"
                                          onsubmit="return confirm('¿Seguro que deseas {{ $user->estaActivo() ? 'desactivar' : 'activar' }} a este usuario?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-sm text-xs border transition {{ $user->estaActivo() ? 'border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950' : 'border-green-200 text-green-700 hover:bg-green-50 dark:border-green-900 dark:text-green-400 dark:hover:bg-green-950' }}">
                                            {{ $user->estaActivo() ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-[#706f6c] dark:text-[#A1A09A]">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
