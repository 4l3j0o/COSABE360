@extends('layouts.app')
@section('titulo','Facturas Electrónicas')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')
<div class="flex justify-between items-center mb-6">
  <h1 class="text-xl font-bold">Facturas Electrónicas SFE (HU-TRIB-02)</h1>
  <span class="text-sm text-muted-foreground">{{ $invoices->total() }} facturas</span>
</div>
@if(session('success')) <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div> @endif
<div class="bg-card border border-border rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="border-b bg-muted/50"><th class="px-4 py-3">Nro</th><th class="px-4 py-3 text-left">Receptor</th><th class="px-4 py-3 text-left">CUF</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Fecha</th></tr></thead>
      <tbody>
        @forelse($invoices as $inv)
        <tr class="border-b hover:bg-muted/20">
          <td class="px-4 py-3 font-mono text-center">{{ $inv->nro_factura }}</td>
          <td class="px-4 py-3">{{ $inv->razon_social_receptor ?? $inv->nit_receptor ?? '-' }}</td>
          <td class="px-4 py-3 font-mono text-xs">{{ Str::limit($inv->cuf,18) }}</td>
          <td class="px-4 py-3 text-right">{{ number_format($inv->total_factura,2) }} Bs</td>
          <td class="px-4 py-3 text-center"><span class="px-2 py-1 rounded text-xs {{ $inv->estado==='AUTORIZADA'?'bg-success/20 text-success':($inv->estado==='CONTINGENCIA'?'bg-warning/20 text-warning':'bg-muted') }}">{{ $inv->estado }}</span></td>
          <td class="px-4 py-3 text-xs">{{ $inv->fecha_emision->format('Y-m-d') }}</td>
        </tr>
        @empty <tr><td colspan="6" class="py-12 text-center text-muted-foreground">Sin facturas. Emita via POST /facturacion/facturas {total_factura, nit_receptor, razon_social_receptor} - IVA/IT 0% EXENTO automático con leyendas</td></tr> @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-4">{{ $invoices->links() }}</div>
</div>
<div class="mt-4 p-4 bg-muted/30 rounded-lg text-xs text-muted-foreground">
  <strong>Flujo:</strong> Al registrar pago mensualidad (HU-19) puede emitir factura exenta. CUF se genera automático, nro_factura secuencial por sucursal (invoice_sequences), leyendas desde tax_configurations. Si tipo_emision=CONTINGENCIA se encola en contingency_queues para reenvío.
</div>
@endsection
