@extends('layouts.app')
@section('titulo','Secuencias Facturación')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<h1 class="text-xl font-bold mb-6">Secuencias de Numeración</h1>
<div class="bg-card border border-border rounded-xl overflow-hidden">
<table class="w-full text-sm"><thead><tr class="border-b bg-muted/50"><th class="px-4 py-3 text-left">Sucursal</th><th class="px-4 py-3">Punto Venta</th><th class="px-4 py-3">Último Nro</th><th class="px-4 py-3">Estado</th></tr></thead>
<tbody>@forelse($sequences as $s)<tr class="border-b"><td class="px-4 py-3">{{ $s->codigo_sucursal }}</td><td class="px-4 py-3 text-center">{{ $s->punto_venta }}</td><td class="px-4 py-3 text-center font-mono">{{ $s->ultimo_numero }}</td><td class="px-4 py-3">{{ $s->estado }}</td></tr>@empty<tr><td colspan="4" class="py-12 text-center text-muted-foreground">Sin secuencias. Se crea automática al emitir primera factura.</td></tr>@endforelse</tbody>
</table>
<div class="p-4">{{ $sequences->links() }}</div>
</div>
@endsection
