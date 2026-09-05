@extends('layouts.app')

@section('titulo', 'RUDE Form I-VI - ' . $student->codigo)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">RUDE — Formulario RM 0684/2025 (Secciones I-VI)</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ $student->nombres }} {{ $student->apellidos }} — {{ $student->codigo }} — Ley 070 Art.68</p>
        </div>
        <a href="{{ route('rude.show', $student) }}" class="px-4 py-2 border border-input rounded-lg bg-background text-foreground text-sm hover:bg-accent">Cancelar</a>
    </div>

    @if($errors->any())
        <div class="bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('rude.update', $student) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Sección I-II: Identidad y CI -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección I-II — Identidad y Documento</h3>
            <p class="text-xs text-muted-foreground mb-4">CI boliviano 7-8 dígitos + complemento (validación), datos del estudiante ya registrados pero editables para corrección RUDE.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">CI *</label>
                    <input type="text" name="numero_documento" value="{{ old('numero_documento', $student->numero_documento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" required>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Complemento</label>
                    <input type="text" name="complemento" value="{{ old('complemento', $student->complemento) }}" placeholder="Ej: 1A, SC" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div class="flex items-end">
                    <p class="text-xs text-muted-foreground">Tipo doc: {{ $student->tipo_documento }} — Validación CI: 7-8 díg + complemento alfanum 1-5.</p>
                </div>
            </div>
        </div>

        <!-- Sección II: Certificado Nacimiento -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección II — Certificado de Nacimiento</h3>
            <p class="text-xs text-muted-foreground mb-4">Si completa uno, debe completar los 5: oficialía, libro, partida, folio y año. Código RUDE es solo lectura (SIE).</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Oficialía</label>
                    <input type="text" name="oficialia_nacimiento" value="{{ old('oficialia_nacimiento', $rude->oficialia_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Libro</label>
                    <input type="text" name="libro_nacimiento" value="{{ old('libro_nacimiento', $rude->libro_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Partida</label>
                    <input type="text" name="partida_nacimiento" value="{{ old('partida_nacimiento', $rude->partida_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Folio</label>
                    <input type="text" name="folio_nacimiento" value="{{ old('folio_nacimiento', $rude->folio_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Año</label>
                    <input type="text" name="anio_nacimiento" value="{{ old('anio_nacimiento', $rude->anio_nacimiento) }}" placeholder="YYYY" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
            <div class="mt-3 bg-muted/30 rounded-lg p-3 text-xs">
                <p class="font-medium">Código RUDE (solo lectura, SIE):</p>
                <p class="font-mono font-bold text-foreground mt-1">{{ $rude->codigo_rude ?? 'POR GENERAR — será asignado por el SIE al enviar a MinEdu' }}</p>
            </div>
        </div>

        <!-- Sección III-IV: Académico -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección III-IV — Datos Académicos</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Código SIE UE *</label>
                    <input type="text" name="codigo_sie_ue" value="{{ old('codigo_sie_ue', $rude->codigo_sie_ue) }}" placeholder="8 dígitos (ej: 12345678)" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <p class="text-xs text-muted-foreground mt-1">Configurable por UE.</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Turno</label>
                    <select name="turno" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        <option value="MANANA" {{ old('turno', $rude->turno) === 'MANANA' ? 'selected' : '' }}>MAÑANA</option>
                        <option value="TARDE" {{ old('turno', $rude->turno) === 'TARDE' ? 'selected' : '' }}>TARDE</option>
                        <option value="NOCTURNO" {{ old('turno', $rude->turno) === 'NOCTURNO' ? 'selected' : '' }}>NOCTURNO</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Zona Residencial</label>
                    <select name="zona_residencial" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        <option value="URBANA" {{ old('zona_residencial', $rude->zona_residencial) === 'URBANA' ? 'selected' : '' }}>URBANA</option>
                        <option value="RURAL" {{ old('zona_residencial', $rude->zona_residencial) === 'RURAL' ? 'selected' : '' }}>RURAL</option>
                        <option value="PERIFERIA" {{ old('zona_residencial', $rude->zona_residencial) === 'PERIFERIA' ? 'selected' : '' }}>PERIFERIA</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Procedencia IE</label>
                    <input type="text" name="procedencia_ie" value="{{ old('procedencia_ie', $rude->procedencia_ie) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="flex items-center gap-2 text-sm"><input type="hidden" name="transporte" value="0"><input type="checkbox" name="transporte" value="1" {{ old('transporte', $rude->transporte) ? 'checked' : '' }} class="rounded"> Transporte</label>
                    <label class="flex items-center gap-2 text-sm"><input type="hidden" name="nuevo_ingreso" value="0"><input type="checkbox" name="nuevo_ingreso" value="1" {{ old('nuevo_ingreso', $rude->nuevo_ingreso ?? true) ? 'checked' : '' }} class="rounded"> Nuevo ingreso</label>
                </div>
            </div>
        </div>

        <!-- Sección V: Cultura/Salud -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección V — Cultura y Salud</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Idioma Originario</label>
                    <input type="text" name="idioma_originario" value="{{ old('idioma_originario', $rude->idioma_originario) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Salud/Discapacidad</label>
                    <input type="text" name="salud_discapacidad" value="{{ old('salud_discapacidad', $rude->salud_discapacidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
        </div>

        <!-- Sección VI: Estado -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección VI — Estado Trámite</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Estado</label>
                    <select name="estado" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="PENDIENTE" {{ old('estado', $rude->estado) === 'PENDIENTE' ? 'selected' : '' }}>pendiente</option>
                        <option value="ENVIADO" {{ old('estado', $rude->estado) === 'ENVIADO' ? 'selected' : '' }}>enviado</option>
                        <option value="APROBADO" {{ old('estado', $rude->estado) === 'APROBADO' ? 'selected' : '' }}>aprobado</option>
                        <option value="RECHAZADO" {{ old('estado', $rude->estado) === 'RECHAZADO' ? 'selected' : '' }}>rechazado</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-medium text-muted-foreground">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">{{ old('observaciones', $rude->observaciones) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90">Guardar RUDE</button>
        </div>
    </form>
@endsection
