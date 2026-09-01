@extends('layouts.app')
@section('titulo','Retenciones RC-IVA')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')
<h1 class="text-xl font-bold mb-6">Retenciones RC-IVA 13% (HU-TRIB-06)</h1>
<div class="bg-card border border-border rounded-xl overflow-hidden">
<table class="w-full text-sm"><thead><tr class="border-b bg-muted/50"><th class="px-4 py-3">Fecha</th><th class="px-4 py-3 text-left">Sujeto</th><th class="px-4 py-3">Base</th><th class="px-4 py-3">Retención 13%</th><th class="px-4 py-3">Estado</th></tr></thead>
<tbody>@forelse($withholdings as $w)<tr class="border-b"><td class="px-4 py-3">{{ $w->fecha->format('Y-m-d') }}</td><td class="px-4 py-3">{{ $w->sujeto_nombre }} <span class="text-xs text-muted-foreground">({{ $w->tipo_sujeto }})</span></td><td class="px-4 py-3 text-right">{{ number_format($w->base_imponible,2) }}</td><td class="px-4 py-3 text-right font-medium">{{ number_format($w->retencion_monto,2) }}</td><td class="px-4 py-3 text-center">{{ $w->estado }}</td></tr>@empty<tr><td colspan="5" class="py-12 text-center text-muted-foreground">Sin retenciones. POST /facturacion/rc-iva {fecha, tipo_sujeto: EMPLEADO|PROVEEDOR, sujeto_id, sujeto_nombre, base_imponible} - retencion 13% auto.</td></tr>@endforelse</tbody>
</table>
<div class="p-4">{{ $withholdings->links() }}</div>
</div>
@endsection
