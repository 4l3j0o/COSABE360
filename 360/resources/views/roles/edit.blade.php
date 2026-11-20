@extends('layouts.app')
@section('titulo', 'Permisos de '.$role->nombre)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('roles.index'), 'texto' => 'Volver a roles y permisos'])
        <div class="mt-1">
            <h1 class="text-xl font-bold text-foreground">Permisos de {{ $role->nombre }}</h1>
            <p class="text-sm text-muted-foreground">Marque los permisos que tendrá este rol.</p>
        </div>
    </div>

    @if($role->nombre === 'Administrador')
        <x-ui.alert type="warning" class="mb-5">
            El rol Administrador conserva todos los permisos. Para evitar bloqueos, no se puede editar.
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            @foreach($permisos as $grupo => $items)
                <div class="bg-card border border-border rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-muted/50 border-b border-border">
                        <h2 class="text-sm font-bold text-foreground">{{ \Illuminate\Support\Str::headline($grupo) }}</h2>
                    </div>
                    <div class="p-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($items as $permiso)
                            @php
                                $marcado = in_array($permiso->name, $asignados, true);
                                $bloqueado = $role->nombre === 'Administrador';
                            @endphp
                            <label class="flex items-start gap-2.5 rounded-lg border border-border/70 p-3 {{ $bloqueado ? 'opacity-70' : 'hover:bg-muted/30 cursor-pointer' }}">
                                <input type="checkbox"
                                       name="permisos[]"
                                       value="{{ $permiso->name }}"
                                       @checked($marcado)
                                       @disabled($bloqueado)
                                       class="mt-0.5 rounded border-input text-primary focus:ring-primary/30">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-foreground break-words">{{ $descripciones[$permiso->name] ?? $permiso->name }}</span>
                                    <span class="block text-[11px] font-mono text-muted-foreground break-all">{{ $permiso->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if($role->nombre !== 'Administrador')
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</a>
                <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                    Guardar permisos
                </button>
            </div>
        @endif
    </form>
@endsection
