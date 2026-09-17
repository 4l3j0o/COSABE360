@extends('layouts.app')
@section('titulo', 'Protección de Datos Personales')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection

@section('contenido')
@php
    $R = \App\Models\ArcoRequest::class;
    $estilos = ['RECIBIDA' => 'bg-warning/10 text-warning', 'EN_PROCESO' => 'bg-info/10 text-info', 'RESUELTA' => 'bg-success/10 text-success', 'RECHAZADA' => 'bg-error/10 text-error'];
    $optsTitular = ['student' => 'Estudiante', 'parent_guardian' => 'Padre/Madre/Tutor'];
@endphp
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Protección de Datos Personales</h1>
        <p class="text-sm text-muted-foreground mt-1">Consentimientos informados y derechos ARCO (Acceso, Rectificación, Cancelación, Oposición).</p>
    </div>
</div>

{{-- Registrar consentimiento --}}
<div class="bg-card border border-border rounded-xl p-5 mb-5">
    <h3 class="font-semibold text-foreground mb-3">Registrar consentimiento informado</h3>
    <form method="POST" action="{{ route('data-protection.consent') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Tipo de titular</label>
            <select name="titular_type" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                @foreach($optsTitular as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Titular</label>
            <select name="titular_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm" id="consent-titular">
                @foreach($students as $s)<option value="{{ $s->id }}" data-tipo="student">{{ $s->apellidos }} {{ $s->nombres }}</option>@endforeach
                @foreach($parents as $p)<option value="{{ $p->id }}" data-tipo="parent_guardian">{{ $p->apellidos }} {{ $p->nombres }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Finalidad</label>
            <input type="text" name="finalidad" placeholder="Ej: Gestión académica, facturación" required class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">¿Consentimiento?</label>
            <select name="consentimiento" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                <option value="1">Sí</option>
                <option value="0">No (revocar)</option>
            </select>
        </div>
        <div class="sm:col-span-4">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">Guardar consentimiento</button>
        </div>
    </form>
</div>

{{-- Registrar solicitud ARCO --}}
<div class="bg-card border border-border rounded-xl p-5 mb-5">
    <h3 class="font-semibold text-foreground mb-3">Registrar solicitud ARCO</h3>
    <form method="POST" action="{{ route('data-protection.request') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Tipo de titular</label>
            <select name="titular_type" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                @foreach($optsTitular as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Titular</label>
            <select name="titular_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->apellidos }} {{ $s->nombres }}</option>@endforeach
                @foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->apellidos }} {{ $p->nombres }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Derecho</label>
            <select name="tipo" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                @foreach($R::TIPOS as $tipo)<option value="{{ $tipo }}">{{ $tipo }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Detalle</label>
            <input type="text" name="detalle" placeholder="Descripción de la solicitud" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
        </div>
        <div class="sm:col-span-4">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:opacity-90 cursor-pointer">Registrar solicitud</button>
        </div>
    </form>
</div>

{{-- Listado de solicitudes ARCO --}}
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm mb-5">
    <div class="px-5 py-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Solicitudes ARCO ({{ $requests->count() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-border bg-muted/50 text-left">
                <th class="px-4 py-3 font-semibold text-foreground">Titular</th>
                <th class="px-4 py-3 font-semibold text-foreground">Derecho</th>
                <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                <th class="px-4 py-3 font-semibold text-foreground">Detalle</th>
                <th class="px-4 py-3 font-semibold text-foreground">Resolver</th>
            </tr></thead>
            <tbody>
                @forelse($requests as $req)
                <tr class="border-b border-border last:border-b-0">
                    <td class="px-4 py-3">{{ $req->titular?->apellidos ?? '' }} {{ $req->titular?->nombres ?? '' }}</td>
                    <td class="px-4 py-3 font-medium text-foreground">{{ $req->tipo }}</td>
                    <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $estilos[$req->estado] ?? 'bg-muted' }}">{{ $req->estado }}</span></td>
                    <td class="px-4 py-3 text-muted-foreground max-w-[200px] truncate">{{ $req->detalle ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('data-protection.resolve', $req) }}" class="flex gap-1 items-center">
                            @csrf
                            <select name="estado" class="px-2 py-1 border border-input rounded-lg text-xs bg-background text-foreground">
                                @foreach($R::ESTADOS as $e)<option value="{{ $e }}" {{ $req->estado === $e ? 'selected' : '' }}>{{ $e }}</option>@endforeach
                            </select>
                            <input type="text" name="resolucion" placeholder="Resolución" class="px-2 py-1 border border-input rounded-lg text-xs bg-background text-foreground">
                            <button type="submit" class="px-2 py-1 rounded-lg text-xs bg-primary text-primary-foreground cursor-pointer">Guardar</button>
                        </form>
                    </td>
                </tr>
                @empty <tr><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Sin solicitudes ARCO.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Listado de consentimientos --}}
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-5 py-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Consentimientos ({{ $consents->count() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-border bg-muted/50 text-left">
                <th class="px-4 py-3 font-semibold text-foreground">Titular</th>
                <th class="px-4 py-3 font-semibold text-foreground">Finalidad</th>
                <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                <th class="px-4 py-3 font-semibold text-foreground">Fecha</th>
            </tr></thead>
            <tbody>
                @forelse($consents as $c)
                <tr class="border-b border-border last:border-b-0">
                    <td class="px-4 py-3">{{ $c->titular?->apellidos ?? '' }} {{ $c->titular?->nombres ?? '' }}</td>
                    <td class="px-4 py-3 text-foreground">{{ $c->finalidad }}</td>
                    <td class="px-4 py-3">
                        @if($c->activo()) <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-success/10 text-success">Consentido</span>
                        @else <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-error/10 text-error">Revocado</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-muted-foreground text-xs">{{ $c->fecha_consentimiento?->format('d/m/Y') ?? ($c->fecha_revocacion?->format('d/m/Y') ?? '—') }}</td>
                </tr>
                @empty <tr><td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Sin consentimientos registrados.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection