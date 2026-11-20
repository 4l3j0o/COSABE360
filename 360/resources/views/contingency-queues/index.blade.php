@extends('layouts.app')
@section('titulo','Cola Contingencia')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<h1 class="text-xl font-bold mb-6">Cola Contingencia - Reenvío Automático</h1>
<div class="bg-card border border-border rounded-xl overflow-hidden">
<table class="w-full text-sm"><thead><tr class="border-b bg-muted/50"><th class="px-4 py-3">Factura ID</th><th class="px-4 py-3">Intentos</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Próx. Intento</th></tr></thead>
<tbody>@forelse($queues as $q)<tr class="border-b"><td class="px-4 py-3 text-center">{{ $q->electronic_invoice_id }}</td><td class="px-4 py-3 text-center">{{ $q->intentos }}/{{ $q->max_intentos }}</td><td class="px-4 py-3 text-center">{{ $q->estado }}</td><td class="px-4 py-3 text-xs">{{ $q->proximo_intento ?? '-' }}</td></tr>@empty<tr><td colspan="4" class="py-12 text-center text-muted-foreground">Cola vacía. Facturas en CONTINGENCIA aparecen aquí automáticamente.</td></tr>@endforelse</tbody>
</table>
<div class="p-4">{{ $queues->links() }}</div>
</div>
@endsection
