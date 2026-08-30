@extends('layouts.app')

@section('titulo', 'RUDE - ' . $student->codigo)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">RUDE — {{ $student->nombres }} {{ $student->apellidos }}</h1>
            <p class="text-sm text-muted-foreground mt-1">Código {{ $student->codigo }} • CI {{ $student->numero_documento }} {{ $student->complemento }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('rude.form', $student) }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Editar Form I-VI</a>
            <a href="{{ route('rude.export', $student) }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">Exportar MinEdu</a>
            <a href="{{ route('rude.index') }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección I - Datos Personales</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">Nombres</dt><dd class="font-medium">{{ $student->nombres }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Apellidos</dt><dd class="font-medium">{{ $student->apellidos }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Fecha Nac.</dt><dd>{{ $student->fecha_nacimiento?->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Sexo</dt><dd>{{ $student->sexo }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">CI</dt><dd class="font-mono">{{ $student->numero_documento }} {{ $student->complemento }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Lugar Nac.</dt><dd>{{ $student->lugar_nacimiento ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección II - Certificado Nacimiento</h3>
            @if($rude && ($rude->oficialia_nacimiento || $rude->codigo_rude))
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-muted-foreground">Código RUDE</dt><dd class="font-mono font-bold">{{ $rude->codigo_rude ?? 'POR GENERAR (SIE)' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Oficialía</dt><dd>{{ $rude->oficialia_nacimiento ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Libro</dt><dd>{{ $rude->libro_nacimiento ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Partida</dt><dd>{{ $rude->partida_nacimiento ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Folio</dt><dd>{{ $rude->folio_nacimiento ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Año</dt><dd>{{ $rude->anio_nacimiento ?? '—' }}</dd></div>
                </dl>
            @else
                <p class="text-sm text-muted-foreground">Sin certificado registrado.</p>
            @endif
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección III-IV - Académico</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">SIE UE</dt><dd class="font-mono">{{ $rude?->codigo_sie_ue ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Turno</dt><dd>{{ $rude?->turno ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Transporte</dt><dd>{{ $rude?->transporte ? 'SI' : 'NO' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Zona</dt><dd>{{ $rude?->zona_residencial ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Procedencia IE</dt><dd>{{ $rude?->procedencia_ie ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Nuevo Ingreso</dt><dd>{{ $rude?->nuevo_ingreso ? 'SI' : 'NO' }}</dd></div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección V-VI - Cultura / Estado</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">Idioma Originario</dt><dd>{{ $rude?->idioma_originario ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Salud/Discapacidad</dt><dd>{{ $rude?->salud_discapacidad ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Estado</dt><dd><span class="px-2 py-0.5 rounded-full text-xs border {{ ($rude?->estado ?? 'PENDIENTE') === 'APROBADO' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">{{ mb_strtolower($rude?->estado ?? 'PENDIENTE') }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Fecha Envío</dt><dd>{{ $rude?->fecha_envio?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Observaciones</dt><dd class="max-w-[200px] truncate">{{ $rude?->observaciones ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-4 bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-sm">
        <p class="font-medium text-amber-700">Nota Ley 070 Art.68:</p>
        <p class="text-muted-foreground mt-1">El código RUDE es generado por el SIE (solo lectura). El código SIE UE es configurable por la unidad educativa.</p>
    </div>
@endsection
