@extends('layouts.app')
@section('titulo','CUFD Diario')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')
<h1 class="text-xl font-bold mb-6">CUFD Diario (daily_cufds)</h1>
<div class="bg-card border border-border rounded-xl overflow-hidden">
<table class="w-full text-sm"><thead><tr class="border-b bg-muted/50"><th class="px-4 py-3 text-left">CUFD</th><th class="px-4 py-3">Vigencia</th><th class="px-4 py-3">Estado</th></tr></thead>
<tbody>@forelse($cufds as $c)<tr class="border-b"><td class="px-4 py-3 font-mono text-xs">{{ Str::limit($c->cufd,40) }}</td><td class="px-4 py-3">{{ $c->fecha_vigencia->format('Y-m-d') }}</td><td class="px-4 py-3">{{ $c->estado }}</td></tr>@empty<tr><td colspan="3" class="py-12 text-center text-muted-foreground">Sin CUFDs.</td></tr>@endforelse</tbody>
</table>
<div class="p-4">{{ $cufds->links() }}</div>
</div>
@endsection
