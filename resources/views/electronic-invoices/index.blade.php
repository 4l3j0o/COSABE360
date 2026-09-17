@extends('layouts.app')
@section('titulo','Facturas Electrónicas')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-bold text-foreground">Facturas Electrónicas SFE</h1>
    <p class="text-xs text-muted-foreground mt-1">Sector Educativo — Exento IVA/IT (Ley 843 Art.13) • PDF profesional listo para <strong>imprimir y compartir</strong> (no hoja blanca genérica)</p>
  </div>
  <span class="inline-flex items-center gap-2 text-sm text-muted-foreground border border-border rounded-full px-3 py-1.5 bg-card">{{ $invoices->total() }} facturas • {{ $invoices->total() >0 ? 'A4 • QR + CUF' : '—' }}</span>
</div>

<div class="bg-card border border-border rounded-xl p-3 mb-4 flex flex-col lg:flex-row gap-3">
  <form method="GET" action="{{ route('electronic-invoices.index') }}" class="flex flex-1 flex-col sm:flex-row gap-2">
    <div class="flex-1 relative">
      <svg class="w-4 h-4 absolute left-2.5 top-2.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="nit_receptor" value="{{ request('nit_receptor') }}" placeholder="Buscar por NIT / receptor / CUF..." class="w-full pl-8 pr-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
    </div>
    <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
      <option value="">Todos los estados</option>
      @foreach(['ENVIADA','AUTORIZADA','CONTINGENCIA','ANULADA','RECHAZADA'] as $e)
        <option value="{{ $e }}" {{ request('estado')===$e?'selected':'' }}>{{ $e }}</option>
      @endforeach
    </select>
    <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity shrink-0">Filtrar</button>
    @if(request('estado') || request('nit_receptor'))
      <a href="{{ route('electronic-invoices.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted text-center">Limpiar</a>
    @endif
  </form>
  <div class="flex items-center gap-2 text-xs text-muted-foreground lg:ml-auto shrink-0">
    <span class="hidden sm:inline">Vista profesional SFE</span>
    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-primary/10 text-primary font-bold"><span class="w-2 h-2 rounded-full bg-success animate-pulse"></span> PDF imprimible</span>
  </div>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="border-b bg-muted/50 text-xs uppercase tracking-wider">
        <th class="px-3 py-3 text-center">Nro</th>
        <th class="px-3 py-3 text-left">Receptor / Cliente</th>
        <th class="px-3 py-3 text-left hidden md:table-cell">CUF / QR</th>
        <th class="px-3 py-3 text-right">Total</th>
        <th class="px-3 py-3 text-center">Estado</th>
        <th class="px-3 py-3 text-center">Fecha</th>
        <th class="px-3 py-3 text-right">Acciones PDF</th>
      </tr></thead>
      <tbody>
        @forelse($invoices as $inv)
        <tr class="border-b hover:bg-muted/20 transition-colors">
          <td class="px-3 py-3 font-mono text-center font-bold text-primary">{{ str_pad($inv->nro_factura,4,'0',STR_PAD_LEFT) }}</td>
          <td class="px-3 py-3">
            <div class="font-medium text-foreground leading-tight">{{ $inv->razon_social_receptor ?: 'S/N' }}</div>
            <div class="text-xs text-muted-foreground font-mono">NIT {{ $inv->nit_receptor ?: ($inv->nro_doc_receptor ?: '0') }} • Suc {{ $inv->codigo_sucursal }}</div>
          </td>
          <td class="px-3 py-3 hidden md:table-cell">
            <div class="font-mono text-[10px] leading-tight max-w-[150px] truncate" title="{{ $inv->cuf }}">{{ Str::limit($inv->cuf,22) }}</div>
            <div class="text-[10px] text-muted-foreground flex items-center gap-1 mt-0.5"><span class="w-2 h-2 rounded-sm bg-primary inline-block"></span> QR + CUF válido</div>
          </td>
          <td class="px-3 py-3 text-right font-mono font-bold text-foreground">{{ number_format($inv->total_factura,2) }} <span class="text-muted-foreground font-normal">Bs</span></td>
          <td class="px-3 py-3 text-center"><span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $inv->estado==='AUTORIZADA'?'bg-success/15 text-success border-success/30':($inv->estado==='CONTINGENCIA'?'bg-warning/15 text-warning border-warning/30':($inv->estado==='ANULADA'?'bg-muted text-muted-foreground border-border':'bg-primary/10 text-primary border-primary/20')) }}">{{ $inv->estado }}</span></td>
          <td class="px-3 py-3 text-center text-xs text-muted-foreground">{{ $inv->fecha_emision->format('d/m/Y') }}<br><span class="font-mono text-[10px]">{{ substr($inv->hora_emision,0,5) }}</span></td>
          <td class="px-3 py-3">
            <div class="flex items-center justify-end gap-1">
              <a href="{{ route('electronic-invoices.pdf', $inv) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary text-primary-foreground rounded-md text-xs font-bold hover:opacity-90 shadow-sm" title="Ver PDF profesional SFE para imprimir o compartir (no genérico)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
                Ver PDF
              </a>
              <a href="{{ route('electronic-invoices.download', $inv) }}" class="inline-flex items-center justify-center w-7 h-7 border border-border rounded-md hover:bg-muted text-muted-foreground hover:text-foreground" title="Descargar PDF">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              </a>
              <button type="button" onclick="compartirFactura('{{ route('electronic-invoices.pdf', $inv) }}', '{{ $inv->nro_factura }}')" class="inline-flex items-center justify-center w-7 h-7 border border-border rounded-md hover:bg-muted text-muted-foreground hover:text-foreground" title="Compartir enlace">↗</button>
            </div>
          </td>
        </tr>
        @empty <tr><td colspan="7" class="py-14 text-center">
          <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center mx-auto mb-3 text-muted-foreground">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14h6m-6-4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <p class="text-sm text-muted-foreground">Sin facturas registradas.</p>
          <p class="text-xs text-muted-foreground mt-1">Emita desde <strong>Pagos de mensualidad</strong> (checkbox <em>Generar Factura</em>) o vía <code class="bg-muted px-1 rounded">POST /facturacion/facturas</code>. Todas son <strong>exentas IVA/IT</strong> con CUF + QR.</p>
        </td></tr> @endforelse
      </tbody>
    </table>
  </div>
  @if($invoices->hasPages())
    <div class="p-3 border-t border-border flex items-center justify-between">
      <span class="text-xs text-muted-foreground">Mostrando {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} de {{ $invoices->total() }}</span>
      <div>{{ $invoices->links() }}</div>
    </div>
  @endif
</div>

<div class="mt-4 grid md:grid-cols-3 gap-3 text-xs">
  <div class="bg-success/5 border border-success/20 rounded-lg p-3">
    <p class="font-bold text-success flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> PDF profesional SFE</p>
    <p class="text-muted-foreground mt-1 leading-relaxed">No es hoja blanca genérica: cabecera verde institucional San Bernardo, datos emisor/receptor, detalle exento, totales, leyendas obligatorias, literal en palabras, QR + CUF verificable, borde y marca de agua CONTINGENCIA/ANULADA. Listo para <strong>imprimir A4</strong> o <strong>compartir por WhatsApp/Email</strong>.</p>
  </div>
  <div class="bg-primary/5 border border-primary/20 rounded-lg p-3">
    <p class="font-bold text-primary">Flujo automático</p>
    <p class="text-muted-foreground mt-1 leading-relaxed">Al registrar <strong>pago de mensualidad</strong> marque <em>Generar Factura</em>: se crea CUF SHA256, nro correlativo por sucursal (<code>invoice_sequences</code>), leyendas desde <em>TaxConfiguration</em> y detalle exento 0% IVA/IT. Contingencia → <code>contingency_queues</code>.</p>
  </div>
  <div class="bg-muted/40 border border-border rounded-lg p-3">
    <p class="font-bold text-foreground">Acciones</p>
    <p class="text-muted-foreground mt-1 leading-relaxed"><strong>Ver PDF</strong> abre en pestaña para imprimir (Ctrl+P). <strong>↓</strong> descarga. <strong>↗ Compartir</strong> copia enlace o abre WhatsApp. Recomendado: guardar PDF en Drive y enviar al tutor.</p>
  </div>
</div>

@push('scripts')
<script>
function compartirFactura(url, nro){
  const texto = 'Factura SFE UE San Bernardo N° ' + nro + ' - ' + url;
  if(navigator.share){
    navigator.share({title:'Factura '+nro, text:texto, url:url}).catch(()=>copiar(url));
  } else if(navigator.clipboard){
    copiar(url);
  } else {
    window.prompt('Copie el enlace:', url);
  }
  function copiar(u){
    navigator.clipboard.writeText(u).then(()=>alert('Enlace copiado: ' + u + '\nPegue en WhatsApp / Email para compartir.'));
  }
}
// Botón WhatsApp directo si quieren, agregar:
function compartirWhats(url, nro){
  window.open('https://wa.me/?text='+encodeURIComponent('Factura SFE N° '+nro+' '+url), '_blank');
}
</script>
@endpush
@endsection
