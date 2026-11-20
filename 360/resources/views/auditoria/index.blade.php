@extends('layouts.app')
@section('titulo', 'Historial de Operaciones')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Historial de Operaciones</h1>
            <p class="text-sm text-muted-foreground">Registro de auditoría: quién, qué acción, en qué módulo y cuándo.</p>
        </div>

        <form method="GET" action="{{ route('auditoria.index') }}" class="bg-card border border-border rounded-xl p-4 flex flex-col lg:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-muted-foreground mb-1">Usuario</label>
                <select name="user_id" class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-background">
                    <option value="">-- Todos los usuarios --</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                            {{ $u->nombre }} {{ $u->apellido }} ({{ $u->rol->nombre ?? '—' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-xs font-medium text-muted-foreground mb-1">Módulo</label>
                <select name="modulo" class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-background">
                    <option value="">-- Todos los módulos --</option>
                    @foreach($modulos as $m)
                        <option value="{{ $m }}" @selected(request('modulo') == $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="border border-border rounded-lg px-3 py-2 text-sm bg-background">
            </div>

            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="border border-border rounded-lg px-3 py-2 text-sm bg-background">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    Filtrar
                </button>
                <a href="{{ route('auditoria.index') }}" class="inline-flex items-center gap-2 border border-border px-4 py-2 rounded-lg text-sm font-medium hover:bg-muted transition-colors">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-primary/10 text-left">
                            <th class="px-4 py-3 font-semibold text-primary">Quién</th>
                            <th class="px-4 py-3 font-semibold text-primary">Acción</th>
                            <th class="px-4 py-3 font-semibold text-primary">Módulo</th>
                            <th class="px-4 py-3 font-semibold text-primary">Cuándo</th>
                            <th class="px-4 py-3 font-semibold text-primary"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($historial as $h)
                            <tr class="hover:bg-muted/50">
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $h->user->nombre ?? 'Sistema' }} {{ $h->user->apellido ?? '' }}</span>
                                    @if($h->user?->rol)
                                        <span class="block text-xs text-muted-foreground">{{ $h->user->rol->nombre }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium">
                                        {{ $h->accion }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $h->modulo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('auditoria.show', $h) }}" class="text-sm text-primary hover:underline">Ver detalle</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">
                                    No se encontraron operaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-center">
            {{ $historial->links() }}
        </div>
    </div>
@endsection