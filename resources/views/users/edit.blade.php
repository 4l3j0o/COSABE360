@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Editar usuario</h1>

        <form method="POST" action="{{ route('users.update', $user) }}"
              class="bg-white dark:bg-[#161615] border border-[#19140035] dark:border-[#3E3E3A] rounded-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nombre" class="block text-sm font-medium mb-1.5">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $user->nombre) }}" required autofocus
                           class="w-full rounded-sm border-[#19140035] dark:border-[#3E3E3A] bg-transparent px-3 py-2 text-sm focus:border-[#1b1b18] dark:focus:border-[#EDEDEC] focus:ring-0">
                    @error('nombre')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="apellido" class="block text-sm font-medium mb-1.5">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="{{ old('apellido', $user->apellido) }}" required
                           class="w-full rounded-sm border-[#19140035] dark:border-[#3E3E3A] bg-transparent px-3 py-2 text-sm focus:border-[#1b1b18] dark:focus:border-[#EDEDEC] focus:ring-0">
                    @error('apellido')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="correo" class="block text-sm font-medium mb-1.5">Correo electrónico</label>
                <input id="correo" name="correo" type="email" value="{{ old('correo', $user->correo) }}" required
                       class="w-full rounded-sm border-[#19140035] dark:border-[#3E3E3A] bg-transparent px-3 py-2 text-sm focus:border-[#1b1b18] dark:focus:border-[#EDEDEC] focus:ring-0">
                @error('correo')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-medium mb-1.5">
                        Contraseña <span class="font-normal text-[#706f6c] dark:text-[#A1A09A]">(dejar vacío para mantener)</span>
                    </label>
                    <input id="password" name="password" type="password"
                           class="w-full rounded-sm border-[#19140035] dark:border-[#3E3E3A] bg-transparent px-3 py-2 text-sm focus:border-[#1b1b18] dark:focus:border-[#EDEDEC] focus:ring-0">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rol_id" class="block text-sm font-medium mb-1.5">Rol</label>
                    <select id="rol_id" name="rol_id"
                            class="w-full rounded-sm border-[#19140035] dark:border-[#3E3E3A] bg-transparent px-3 py-2 text-sm focus:border-[#1b1b18] dark:focus:border-[#EDEDEC] focus:ring-0">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" @selected(old('rol_id', $user->rol_id) == $rol->id)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('rol_id')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium mb-1.5">Teléfono <span class="font-normal text-[#706f6c] dark:text-[#A1A09A]">(opcional)</span></label>
                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $user->telefono) }}"
                       class="w-full rounded-sm border-[#19140035] dark:border-[#3E3E3A] bg-transparent px-3 py-2 text-sm focus:border-[#1b1b18] dark:focus:border-[#EDEDEC] focus:ring-0">
                @error('telefono')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-[#19140015] dark:border-[#3E3E3A]/50">
                <a href="{{ route('users.index') }}"
                   class="px-4 py-2 text-sm border border-[#19140035] dark:border-[#3E3E3A] hover:border-[#1915014a] rounded-sm transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] rounded-sm text-sm font-medium hover:opacity-90 transition">
                    Actualizar usuario
                </button>
            </div>
        </form>
    </div>
@endsection
