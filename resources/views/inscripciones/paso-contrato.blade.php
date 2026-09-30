@extends('layouts.app')
@section('titulo', 'Paso 4 — Contrato en Línea')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="mb-6">
    <a href="{{ route('inscripciones.mi-inscripcion') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver a Mi Inscripción
    </a>
    <h1 class="text-xl font-bold text-foreground mt-2">Paso 4 — Aceptar el contrato en línea</h1>
    <p class="text-sm text-muted-foreground mt-1">Complete los datos de facturación, lea el contrato y acéptelo. Además imprímalo y entréguelo firmado en secretaría.</p>
</div>

{{-- Los errores de validación se muestran como tarjetas abajo a la derecha (layouts._toasts). --}}

<form method="POST" action="{{ route('inscripciones.contrato.aceptar') }}" class="bg-card border border-border rounded-xl p-4 mb-5">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Curso</label>
            <input type="text" value="{{ trim(($curso?->grade?->level?->nombre ?? '') . ' - ' . ($curso?->grade?->nombre ?? '')) }}" readonly class="px-3 py-2 border border-input rounded-lg bg-muted text-foreground text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">NIT para la facturación *</label>
            <input type="text" name="nit_facturacion" value="{{ old('nit_facturacion', $inscripcion?->nit_facturacion) }}" required maxlength="20" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Razón Social para la facturación *</label>
            <input type="text" name="razon_social_facturacion" value="{{ old('razon_social_facturacion', $inscripcion?->razon_social_facturacion) }}" required maxlength="200" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm w-full">
        </div>
        <div class="sm:col-span-3">
            <label class="block text-xs font-semibold text-muted-foreground mb-1">Suscriptor del contrato</label>
            <input type="text" value="{{ trim(($tutor->apellidos ?? '') . ' ' . ($tutor->nombres ?? '')) }}" readonly class="px-3 py-2 border border-input rounded-lg bg-muted text-foreground text-sm w-full">
        </div>
    </div>

    <div class="border border-border rounded-lg p-4 mb-4 text-sm text-foreground leading-relaxed overflow-y-auto" style="max-height: 420px;">
        @include('inscripciones.pdf._contrato-cuerpo')
    </div>

    <label class="flex items-start gap-2 text-sm cursor-pointer">
        <input type="checkbox" name="acepto" value="1" required class="mt-1 accent-primary">
        <span><strong>ACEPTO.</strong> Debe aceptar todas las cláusulas para continuar.</span>
    </label>

    <button type="submit" class="mt-4 px-6 py-2.5 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90">Aceptar contrato</button>
</form>
@endsection
