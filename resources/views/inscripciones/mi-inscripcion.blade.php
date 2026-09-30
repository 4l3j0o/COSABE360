@extends('layouts.app')
@section('titulo', 'Mi Inscripción en Línea')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="mb-6">
    <h1 class="text-xl font-bold text-foreground">Mi Inscripción en Línea</h1>
    <p class="text-sm text-muted-foreground mt-1">{{ $student->apellidos }} {{ $student->nombres }} · Código {{ $student->codigo }} · Estado: <strong>{{ $inscripcion?->estado === 'COMPLETADA' ? 'inscrito' : 'preinscripción' }}</strong></p>
</div>

@if($inscripcion?->linea_completada_en)
    <div class="bg-primary/10 border border-primary/20 text-primary rounded-xl p-4 mb-5 text-sm">
        Inscripción en línea finalizada el {{ $inscripcion->linea_completada_en->format('d/m/Y') }}. Ya no admite cambios.
    </div>
@endif

{{-- Pasos --}}
<div class="grid grid-cols-1 sm:grid-cols-5 gap-3 mb-6">
    @foreach($pasos as $n => $paso)
        <a href="{{ route($paso['ruta']) }}" class="bg-card border {{ $paso['completo'] ? 'border-success/40' : 'border-border' }} rounded-xl p-4 text-center hover:shadow-sm transition-shadow">
            <p class="text-2xl font-bold {{ $paso['completo'] ? 'text-success' : 'text-muted-foreground' }}">{{ $paso['completo'] ? '✓' : $n }}</p>
            <p class="text-xs font-medium text-foreground mt-1">Paso {{ $n }}<br>{{ $paso['nombre'] }}</p>
        </a>
    @endforeach
</div>

{{-- Contrato y anexo --}}
<div class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-center">
    <div>
        <h3 class="font-semibold text-foreground">Contrato y anexo</h3>
        <p class="text-xs text-muted-foreground">Descargue, imprima y entregue firmados por el padre/tutor en dirección.</p>
    </div>
    <div class="flex gap-2 ml-auto">
        <a href="{{ route('inscripciones.contrato', $student) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Contrato PDF</a>
        <a href="{{ route('inscripciones.anexo', $student) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Anexo PDF</a>
    </div>
</div>

{{-- Documentos paso 3 --}}
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-border">
        <h3 class="font-semibold text-foreground">Paso 3 — Documentos</h3>
        <p class="text-xs text-muted-foreground">PDF/JPG/PNG, máx 5MB por archivo.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50 text-left">
                    <th class="px-4 py-3 font-semibold text-foreground">Documento</th>
                    <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                    <th class="px-4 py-3 font-semibold text-foreground">Archivo</th>
                    <th class="px-4 py-3 text-center font-semibold text-foreground">Subir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pasos[3]['docs'] ?? [] as $d)
                    <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $d['nombre'] }}</td>
                        <td class="px-4 py-3">
                            @php
                                $estado = $d['doc']?->estado ?? 'PENDIENTE';
                                $color = match($estado) {
                                    'VERIFICADO' => 'bg-success/10 text-success',
                                    'ENTREGADO' => 'bg-blue-500/10 text-blue-500',
                                    default => 'bg-muted text-muted-foreground',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">{{ strtolower($estado) }}</span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground text-xs">
                            @if($d['doc']?->archivo && ! str_starts_with($d['doc']->archivo, 'documentos/'))
                                <a href="{{ asset('storage/' . $d['doc']->archivo) }}" target="_blank" class="underline">ver archivo</a>
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(! $inscripcion?->linea_completada_en)
                            <form method="POST" action="{{ route('inscripciones.subir-documento', $student) }}" enctype="multipart/form-data" class="flex gap-2 justify-center">
                                @csrf
                                <input type="hidden" name="tipo_documento" value="{{ $d['tipo'] }}">
                                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required class="text-xs text-muted-foreground file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:opacity-90 cursor-pointer">Subir</button>
                            </form>
                            @else <span class="text-xs text-muted-foreground">Finalizado</span> @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
