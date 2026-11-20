@extends('layouts.app')
@section('titulo', 'Paso 5 — Finalización')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="mb-6">
    @include('layouts._volver', ['href' => route('inscripciones.mi-inscripcion'), 'texto' => 'Volver a Mi Inscripción'])
    <h1 class="text-xl font-bold text-foreground mt-2">Paso 5 — Finalización</h1>
    <p class="text-sm text-muted-foreground mt-1">Revise los datos antes de finalizar su registro. Una vez finalizado no podrá realizar cambios.</p>
</div>

{{-- Los avisos se muestran como tarjetas abajo a la derecha (layouts._toasts). --}}

<div class="bg-card border border-border rounded-xl p-4 mb-5 text-sm">
    <h3 class="font-semibold text-foreground mb-3">Resumen</h3>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
        <div class="flex justify-between"><dt class="text-muted-foreground">Estudiante</dt><dd class="text-foreground font-medium">{{ $student->apellidos }} {{ $student->nombres }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">Código</dt><dd class="text-foreground font-medium">{{ $student->codigo }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">CI</dt><dd class="text-foreground">{{ trim(($student->numero_documento ?? '') . ' ' . ($student->complemento ?? '')) }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">RUDE</dt><dd class="text-foreground">{{ $student->rudeRecord?->codigo_rude ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">Curso</dt><dd class="text-foreground">{{ $inscripcion?->registration?->section?->codigoCorto() ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">Domicilio</dt><dd class="text-foreground">{{ $student->rudeRecord?->dir_latitud && $student->rudeRecord?->dir_longitud ? $student->rudeRecord->dir_latitud . ', ' . $student->rudeRecord->dir_longitud : '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">NIT facturación</dt><dd class="text-foreground">{{ $inscripcion?->nit_facturacion ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">Razón social</dt><dd class="text-foreground">{{ $inscripcion?->razon_social_facturacion ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-muted-foreground">Contrato aceptado</dt><dd class="text-foreground">{{ $inscripcion?->contrato_aceptado_en ? $inscripcion->contrato_aceptado_en->format('d/m/Y H:i') : '—' }}</dd></div>
    </dl>

    <h4 class="font-semibold text-foreground mt-4 mb-2">Documentos</h4>
    <ul class="space-y-1">
        @foreach($docs as $d)
            <li class="flex justify-between">
                <span class="text-muted-foreground">{{ $d['nombre'] }}</span>
                <span class="{{ $d['cargado'] ? 'text-success font-medium' : 'text-error font-medium' }}">{{ $d['cargado'] ? 'Cargado' : 'Falta' }}</span>
            </li>
        @endforeach
    </ul>
</div>

<form method="POST" action="{{ route('inscripciones.finalizar.confirmar') }}" class="bg-card border border-border rounded-xl p-4">
    @csrf
    <label class="flex items-start gap-2 text-sm cursor-pointer">
        <input type="checkbox" name="consentimiento" value="1" required class="mt-1 accent-primary">
        <span>Doy mi consentimiento para que estos datos se almacenen en la base de datos del colegio.</span>
    </label>
    <button type="submit" class="mt-4 px-6 py-2.5 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90">Finalizar inscripción</button>
</form>
@endsection
