@extends('layouts.app')
@section('titulo','Eventos Significativos')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<h1 class="text-xl font-bold mb-6">Eventos Significativos (significant_events)</h1>
<div class="bg-card border border-border rounded-xl overflow-hidden">
<table class="w-full text-sm"><thead><tr class="border-b bg-muted/50"><th class="px-4 py-3 text-left">Tipo</th><th class="px-4 py-3">Inicio</th><th class="px-4 py-3">Estado</th></tr></thead>
<tbody>@forelse($events as $e)<tr class="border-b"><td class="px-4 py-3">{{ $e->tipo_evento }}</td><td class="px-4 py-3">{{ $e->fecha_inicio }}</td><td class="px-4 py-3">{{ $e->estado }}</td></tr>@empty<tr><td colspan="3" class="py-12 text-center text-muted-foreground">Sin eventos.</td></tr>@endforelse</tbody>
</table>
<div class="p-4">{{ $events->links() }}</div>
</div>
@endsection
