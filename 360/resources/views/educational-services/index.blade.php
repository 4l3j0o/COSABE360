@extends('layouts.app')
@section('titulo','Servicios Educativos')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<div class="flex justify-between items-center mb-6">
  <h1 class="text-xl font-bold">Catálogo Servicios / Productos SIN (RF29)</h1>
  <span class="text-sm text-muted-foreground">{{ $services->total() }} registros</span>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="border-b bg-muted/50"><th class="px-4 py-3 text-left">Código SIN</th><th class="px-4 py-3 text-left">Descripción</th><th class="px-4 py-3">IVA</th><th class="px-4 py-3">Estado</th></tr></thead>
      <tbody>
        @forelse($services as $s)
        <tr class="border-b"><td class="px-4 py-3 font-mono">{{ $s->codigo_sin }}</td><td class="px-4 py-3">{{ $s->descripcion }}</td><td class="px-4 py-3 text-center">{{ $s->iva_tipo }} {{ $s->iva_tasa }}%</td><td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs {{ $s->estado==='ACTIVO'?'bg-success/20 text-success':'bg-muted text-muted-foreground' }}">{{ $s->estado }}</span></td></tr>
        @empty <tr><td colspan="4" class="px-4 py-12 text-center text-muted-foreground">Sin servicios. Seed EducationalServiceSeeder o cree via API POST /facturacion/servicios</td></tr> @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-4">{{ $services->links() }}</div>
</div>
@endsection
