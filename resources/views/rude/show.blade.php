@extends('layouts.app')

@section('titulo', 'RUDE - ' . $student->codigo)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $R = \App\Models\StudentRudeRecord::class;
        $d = fn($v) => $v ?? '—';
        $si = fn($v) => $v ? 'SI' : 'NO';
        $arr = fn($v) => $d($R::listar($v));
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">RUDE — {{ $student->nombres }} {{ $student->apellidos }}</h1>
            <p class="text-sm text-muted-foreground mt-1">Código {{ $student->codigo }} • CI {{ $student->numero_documento }} {{ $student->complemento }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('rude.form', $student) }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Editar Form I-V</a>
            <a href="{{ route('rude.export', $student) }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">Exportar MinEdu</a>
            <a href="{{ route('rude.export-pdf', $student) }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">PDF</a>
            <a href="{{ route('rude.index') }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección I - Unidad Educativa</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">SIE UE</dt><dd class="font-mono">{{ $d($rude?->codigo_sie_ue) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Fecha registro</dt><dd>{{ $rude?->fecha_registro?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Localidad</dt><dd>{{ $d($rude?->ue_localidad) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Nombre(s)</dt><dd>{{ $d($rude?->ue_nombres) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Apellidos</dt><dd>{{ trim(($rude?->ue_apellido_paterno ?? '') . ' ' . ($rude?->ue_apellido_materno ?? '')) ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">País / Depto.</dt><dd>{{ trim(($rude?->ue_pais ?? '') . ' / ' . ($rude?->ue_departamento ?? '')) ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Provincia / Municipio</dt><dd>{{ trim(($rude?->ue_provincia ?? '') . ' / ' . ($rude?->ue_municipio ?? '')) ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Zona / Calle</dt><dd>{{ trim(($rude?->ue_zona ?? '') . ' / ' . ($rude?->ue_calle ?? '')) ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Teléfono / Celular</dt><dd>{{ trim(($rude?->ue_telefono ?? '') . ' / ' . ($rude?->ue_celular ?? '')) ?: '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección II - Estudiante</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">Código RUDE</dt><dd class="font-mono font-bold">{{ $rude->codigo_rude ?? 'POR GENERAR (SIE)' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Nombres / Apellidos</dt><dd>{{ $student->nombres }} {{ $student->apellidos }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Lugar Nac.</dt><dd>{{ $d($student->lugar_nacimiento) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Fecha Nac.</dt><dd>{{ $student->fecha_nacimiento?->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Sexo</dt><dd>{{ $d($student->sexo) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Documento</dt><dd class="font-mono">{{ $student->numero_documento }} {{ $student->complemento }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Discapacidad</dt><dd>{{ $d($student->discapacidad) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">N.º Registro Discap. IBC</dt><dd>{{ $d($rude?->nro_registro_discapacidad) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Certificado (O/L/P/F/A)</dt><dd class="font-mono text-xs">{{ $d(trim(($rude?->oficialia_nacimiento ?? '') . ' / ' . ($rude?->libro_nacimiento ?? '') . ' / ' . ($rude?->partida_nacimiento ?? '') . ' / ' . ($rude?->folio_nacimiento ?? '') . ' / ' . ($rude?->anio_nacimiento ?? ''))) }}</dd></div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección III - Dirección Actual</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">Departamento</dt><dd>{{ $d($rude?->dir_departamento) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Provincia / Municipio</dt><dd>{{ trim(($rude?->dir_provincia ?? '') . ' / ' . ($rude?->dir_municipio ?? '')) ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Comunidad</dt><dd>{{ $d($rude?->dir_comunidad) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Zona / Calle</dt><dd>{{ trim(($rude?->dir_zona ?? '') . ' / ' . ($rude?->dir_calle ?? '')) ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">N.º vivienda</dt><dd>{{ $d($rude?->dir_nro_vivienda) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Teléfono / Celular</dt><dd>{{ trim(($rude?->dir_telefono ?? '') . ' / ' . ($rude?->dir_celular ?? '')) ?: '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección IV - Aspectos Socioeconómicos</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">4.1 Idioma aprendió</dt><dd>{{ $d($rude?->idioma_aprendio) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.1 Uso frecuente</dt><dd class="text-right">{{ $arr($rude?->idiomas_uso_frecuente) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.1 Autoidentificación</dt><dd>{{ $d($rude?->autoidentificacion_cultural) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.2 Salud / Seguro</dt><dd>{{ $d($rude?->salud_seguro) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.2 Veces al Centro</dt><dd>{{ $d($rude?->salud_veces) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.3 Baño / Vivienda</dt><dd>{{ $d($rude?->serv_bano) . ' / ' . $d($rude?->serv_vivienda_tipo) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.4 Internet</dt><dd>{{ $arr($rude?->internet_lugares) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.5 Laboral</dt><dd>{{ $si($rude?->laboral_trabajo) . ' / ' . $d($rude?->laboral_actividad) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.6 Transporte</dt><dd>{{ $d($rude?->transporte_medio) . ' / ' . $d($rude?->transporte_tiempo) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.7 Abandono</dt><dd>{{ $si($rude?->abandono_escolar) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.8 Discapacidad</dt><dd class="text-right">{{ $arr($rude?->discapacidad_tipos) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">4.8 Dificultades</dt><dd class="text-right">{{ $arr($rude?->dificultad_aprendizaje) }}</dd></div>
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Sección V - Convivencia y Tutores</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">Vive con</dt><dd class="text-right">{{ $arr($rude?->convive_con) }}</dd></div>
                @forelse($student->parentGuardians as $pg)
                    <div class="flex justify-between"><dt class="text-muted-foreground">{{ $pg->pivot?->tipo_relacion ?? 'Tutor' }}</dt><dd class="text-right">{{ $pg->nombres }} {{ $pg->apellidos }}</dd></div>
                @empty
                    <div class="flex justify-between"><dt class="text-muted-foreground">Tutores</dt><dd>Sin tutores registrados.</dd></div>
                @endforelse
            </dl>
        </div>

        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-3">Estado del Trámite</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted-foreground">Estado</dt><dd><span class="px-2 py-0.5 rounded-full text-xs border {{ ($rude?->estado ?? 'PENDIENTE') === 'APROBADO' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">{{ mb_strtolower($rude?->estado ?? 'PENDIENTE') }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Fecha Envío</dt><dd>{{ $rude?->fecha_envio?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Fecha Respuesta</dt><dd>{{ $rude?->fecha_respuesta?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted-foreground">Observaciones</dt><dd class="max-w-[200px] truncate">{{ $rude?->observaciones ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-4 bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-sm">
        <p class="font-medium text-amber-700">Nota Ley 070 Art.68 / RM 0031/2025:</p>
        <p class="text-muted-foreground mt-1">El código RUDE es generado por el SIE (solo lectura). El código SIE UE es configurable por la unidad educativa. Formulario alineado al RUDE oficial vigente (Resolución Ministerial Nº 0031/2025).</p>
    </div>
@endsection