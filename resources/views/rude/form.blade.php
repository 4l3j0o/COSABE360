@extends('layouts.app')

@section('titulo', 'RUDE Form I-V - ' . $student->codigo)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    @php
        $R = \App\Models\StudentRudeRecord::class;
        $check = fn($arr, $val) => is_array($arr) && in_array($val, $arr);
        $sino = fn($v) => $v === true || $v === 1 || $v === '1';
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">RUDE — Formulario Oficial RM 0031/2025 (Secciones I-V)</h1>
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

        <!-- Sección I: Unidad Educativa -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección I — Datos de la Unidad Educativa</h3>
            <p class="text-xs text-muted-foreground mb-4">Código SIE UE (8 dígitos) + datos de la unidad educativa.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Código SIE UE</label>
                    <input type="text" name="codigo_sie_ue" value="{{ old('codigo_sie_ue', $rude->codigo_sie_ue) }}" placeholder="8 dígitos" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Fecha de llenado</label>
                    <input type="date" name="fecha_registro" value="{{ old('fecha_registro', $rude->fecha_registro?->format('Y-m-d')) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Localidad</label>
                    <input type="text" name="ue_localidad" value="{{ old('ue_localidad', $rude->ue_localidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Nombre(s)</label>
                    <input type="text" name="ue_nombres" value="{{ old('ue_nombres', $rude->ue_nombres) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Apellido Paterno</label>
                    <input type="text" name="ue_apellido_paterno" value="{{ old('ue_apellido_paterno', $rude->ue_apellido_paterno) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Apellido Materno</label>
                    <input type="text" name="ue_apellido_materno" value="{{ old('ue_apellido_materno', $rude->ue_apellido_materno) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">País</label>
                    <input type="text" name="ue_pais" value="{{ old('ue_pais', $rude->ue_pais) }}" placeholder="Bolivia" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Departamento</label>
                    <input type="text" name="ue_departamento" value="{{ old('ue_departamento', $rude->ue_departamento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Provincia</label>
                    <input type="text" name="ue_provincia" value="{{ old('ue_provincia', $rude->ue_provincia) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Municipio</label>
                    <input type="text" name="ue_municipio" value="{{ old('ue_municipio', $rude->ue_municipio) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Comunidad</label>
                    <input type="text" name="ue_comunidad" value="{{ old('ue_comunidad', $rude->ue_comunidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Zona</label>
                    <input type="text" name="ue_zona" value="{{ old('ue_zona', $rude->ue_zona) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Calle</label>
                    <input type="text" name="ue_calle" value="{{ old('ue_calle', $rude->ue_calle) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">N.º vivienda</label>
                    <input type="text" name="ue_nro_vivienda" value="{{ old('ue_nro_vivienda', $rude->ue_nro_vivienda) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Teléfono fijo</label>
                    <input type="text" name="ue_telefono" value="{{ old('ue_telefono', $rude->ue_telefono) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Celular de contacto</label>
                    <input type="text" name="ue_celular" value="{{ old('ue_celular', $rude->ue_celular) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
        </div>

        <!-- Sección II: Estudiante -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección II — Datos de la o el Estudiante</h3>
            <p class="text-xs text-muted-foreground mb-4">CI 7-8 dígitos + complemento; certificado completo (si uno, todos); código RUDE solo lectura (SIE).</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Tipo Documento</label>
                    <select name="tipo_documento" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::DOCUMENTOS as $val => $label)
                            <option value="{{ $val }}" {{ old('tipo_documento', $student->tipo_documento) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">CI / N.º Documento</label>
                    <input type="text" name="numero_documento" value="{{ old('numero_documento', $student->numero_documento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Complemento</label>
                    <input type="text" name="complemento" value="{{ old('complemento', $student->complemento) }}" placeholder="Ej: 1A, SC" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div class="flex items-end">
                    <p class="text-xs text-muted-foreground">Sexo: {{ $student->sexo }}</p>
                </div>
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
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Discapacidad</label>
                    <input type="text" name="discapacidad" value="{{ old('discapacidad', $student->discapacidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">N.º Registro Discapacidad IBC</label>
                    <input type="text" name="nro_registro_discapacidad" value="{{ old('nro_registro_discapacidad', $rude->nro_registro_discapacidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
            <div class="mt-3 bg-muted/30 rounded-lg p-3 text-xs">
                <p class="font-medium">Código RUDE (solo lectura, SIE):</p>
                <p class="font-mono font-bold text-foreground mt-1">{{ $rude->codigo_rude ?? 'POR GENERAR — será asignado por el SIE al enviar a MinEdu' }}</p>
            </div>
        </div>

        <!-- Sección III: Dirección actual -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección III — Dirección Actual del Estudiante</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><label class="text-xs font-medium text-muted-foreground">Departamento</label><input type="text" name="dir_departamento" value="{{ old('dir_departamento', $rude->dir_departamento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Provincia</label><input type="text" name="dir_provincia" value="{{ old('dir_provincia', $rude->dir_provincia) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Municipio</label><input type="text" name="dir_municipio" value="{{ old('dir_municipio', $rude->dir_municipio) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Comunidad</label><input type="text" name="dir_comunidad" value="{{ old('dir_comunidad', $rude->dir_comunidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Zona/Villa</label><input type="text" name="dir_zona" value="{{ old('dir_zona', $rude->dir_zona) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Avenida/Calle</label><input type="text" name="dir_calle" value="{{ old('dir_calle', $rude->dir_calle) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">N.º vivienda</label><input type="text" name="dir_nro_vivienda" value="{{ old('dir_nro_vivienda', $rude->dir_nro_vivienda) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Teléfono fijo</label><input type="text" name="dir_telefono" value="{{ old('dir_telefono', $rude->dir_telefono) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
                <div><label class="text-xs font-medium text-muted-foreground">Celular de contacto</label><input type="text" name="dir_celular" value="{{ old('dir_celular', $rude->dir_celular) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm"></div>
            </div>
        </div>

        <!-- Sección IV: Socioeconómico -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-4">Sección IV — Aspectos Socioeconómicos</h3>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.1 Idioma y autoidentificación cultural</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Idioma en el que aprendió a hablar</label>
                    <select name="idioma_aprendio" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::IDIOMAS as $idioma)
                            <option value="{{ $idioma }}" {{ old('idioma_aprendio', $rude->idioma_aprendio) === $idioma ? 'selected' : '' }}>{{ $idioma }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Idioma(s) de uso frecuente</label>
                    <select name="idiomas_uso_frecuente[]" multiple class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        @foreach($R::IDIOMAS as $idioma)
                            <option value="{{ $idioma }}" {{ $check(old('idiomas_uso_frecuente', $rude->idiomas_uso_frecuente), $idioma) ? 'selected' : '' }}>{{ $idioma }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Autoidentificación cultural</label>
                    <select name="autoidentificacion_cultural" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::AUTOIDENTIFICACIONES as $auto)
                            <option value="{{ $auto }}" {{ old('autoidentificacion_cultural', $rude->autoidentificacion_cultural) === $auto ? 'selected' : '' }}>{{ $auto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.2 Salud</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Centro de Salud/Posta/Hospital en su comunidad?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="salud_centro_cercano" value="1" {{ $sino(old('salud_centro_cercano', $rude->salud_centro_cercano)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="salud_centro_cercano" value="0" {{ old('salud_centro_cercano', $rude->salud_centro_cercano) === 0 || $rude->salud_centro_cercano === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Acudió por problemas de salud el año pasado?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="salud_atendido_anio" value="1" {{ $sino(old('salud_atendido_anio', $rude->salud_atendido_anio)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="salud_atendido_anio" value="0" {{ old('salud_atendido_anio', $rude->salud_atendido_anio) === 0 || $rude->salud_atendido_anio === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Cuántas veces fue al Centro de Salud?</label>
                    <input type="number" name="salud_veces" min="0" max="365" value="{{ old('salud_veces', $rude->salud_veces) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Tiene seguro de salud?</label>
                    <select name="salud_seguro" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::SEGUROS_SALUD as $seguro)
                            <option value="{{ $seguro }}" {{ old('salud_seguro', $rude->salud_seguro) === $seguro ? 'selected' : '' }}>{{ $seguro }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.3 Acceso a servicios básicos</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Agua potable por cañería de red?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_agua_red" value="1" {{ $sino(old('serv_agua_red', $rude->serv_agua_red)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_agua_red" value="0" {{ old('serv_agua_red', $rude->serv_agua_red) === 0 || $rude->serv_agua_red === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Tiene baño en su vivienda?</label>
                    <select name="serv_bano" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::BANOS as $bano)
                            <option value="{{ $bano }}" {{ old('serv_bano', $rude->serv_bano) === $bano ? 'selected' : '' }}>{{ $bano }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Red de alcantarillado?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_alcantarillado" value="1" {{ $sino(old('serv_alcantarillado', $rude->serv_alcantarillado)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_alcantarillado" value="0" {{ old('serv_alcantarillado', $rude->serv_alcantarillado) === 0 || $rude->serv_alcantarillado === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Usa energía eléctrica para alumbrar?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_electricidad" value="1" {{ $sino(old('serv_electricidad', $rude->serv_electricidad)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_electricidad" value="0" {{ old('serv_electricidad', $rude->serv_electricidad) === 0 || $rude->serv_electricidad === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Servicio de recojo de basura?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_recojo_basura" value="1" {{ $sino(old('serv_recojo_basura', $rude->serv_recojo_basura)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_recojo_basura" value="0" {{ old('serv_recojo_basura', $rude->serv_recojo_basura) === 0 || $rude->serv_recojo_basura === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">La vivienda que ocupa el hogar es:</label>
                    <select name="serv_vivienda_tipo" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::TIPOS_VIVIENDA as $tipo)
                            <option value="{{ $tipo }}" {{ old('serv_vivienda_tipo', $rude->serv_vivienda_tipo) === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.4 Acceso a internet</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Dónde accede a internet?</label>
                    <div class="grid grid-cols-2 gap-2 pt-2 text-sm">
                        @foreach(['Su vivienda', 'Lugares públicos', 'La Unidad Educativa', 'Teléfono celular', 'No necesita internet'] as $lugar)
                            <label class="flex items-center gap-2"><input type="checkbox" name="internet_lugares[]" value="{{ $lugar }}" {{ $check(old('internet_lugares', $rude->internet_lugares), $lugar) ? 'checked' : '' }}> {{ $lugar }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Con qué frecuencia usa internet?</label>
                    <select name="internet_frecuencia" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::FRECUENCIAS_INTERNET as $frec)
                            <option value="{{ $frec }}" {{ old('internet_frecuencia', $rude->internet_frecuencia) === $frec ? 'selected' : '' }}>{{ $frec }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.5 Actividad laboral</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿El estudiante trabajó?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_trabajo" value="1" {{ $sino(old('laboral_trabajo', $rude->laboral_trabajo)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_trabajo" value="0" {{ old('laboral_trabajo', $rude->laboral_trabajo) === 0 || $rude->laboral_trabajo === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿En qué actividad trabajó?</label>
                    <select name="laboral_actividad" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::ACTIVIDADES_LABORALES as $act)
                            <option value="{{ $act }}" {{ old('laboral_actividad', $rude->laboral_actividad) === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿En qué turno(s) trabajó?</label>
                    <div class="grid grid-cols-3 gap-2 pt-2 text-sm">
                        @foreach(['Mañana', 'Tarde', 'Noche'] as $turno)
                            <label class="flex items-center gap-1"><input type="checkbox" name="laboral_turnos[]" value="{{ $turno }}" {{ $check(old('laboral_turnos', $rude->laboral_turnos), $turno) ? 'checked' : '' }}> {{ $turno }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Con qué frecuencia trabajó?</label>
                    <select name="laboral_frecuencia" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::FRECUENCIAS_LABORAL as $frec)
                            <option value="{{ $frec }}" {{ old('laboral_frecuencia', $rude->laboral_frecuencia) === $frec ? 'selected' : '' }}>{{ $frec }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Recibió algún pago?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_recibio_pago" value="1" {{ $sino(old('laboral_recibio_pago', $rude->laboral_recibio_pago)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_recibio_pago" value="0" {{ old('laboral_recibio_pago', $rude->laboral_recibio_pago) === 0 || $rude->laboral_recibio_pago === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.6 Medio de transporte</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Cómo llega a la UE?</label>
                    <select name="transporte_medio" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::MEDIOS_TRANSPORTE as $medio)
                            <option value="{{ $medio }}" {{ old('transporte_medio', $rude->transporte_medio) === $medio ? 'selected' : '' }}>{{ $medio }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Tiempo máximo de llegada</label>
                    <select name="transporte_tiempo" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::TIEMPOS_TRANSPORTE as $tiempo)
                            <option value="{{ $tiempo }}" {{ old('transporte_tiempo', $rude->transporte_tiempo) === $tiempo ? 'selected' : '' }}>{{ $tiempo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.7 Abandono escolar</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">¿Abandonó la UE el año pasado?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="abandono_escolar" value="1" {{ $sino(old('abandono_escolar', $rude->abandono_escolar)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="abandono_escolar" value="0" {{ old('abandono_escolar', $rude->abandono_escolar) === 0 || $rude->abandono_escolar === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Razones del abandono escolar</label>
                    <div class="grid grid-cols-2 gap-2 pt-2 text-sm">
                        @foreach($R::RAZONES_ABANDONO as $razon)
                            <label class="flex items-center gap-2"><input type="checkbox" name="abandono_razones[]" value="{{ $razon }}" {{ $check(old('abandono_razones', $rude->abandono_razones), $razon) ? 'checked' : '' }}> {{ $razon }}</label>
                        @endforeach
                    </div>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.8 Características particulares</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Tipos de discapacidad</label>
                    <div class="grid grid-cols-2 gap-2 pt-2 text-sm">
                        @foreach($R::TIPOS_DISCAPACIDAD as $tipo)
                            <label class="flex items-center gap-2"><input type="checkbox" name="discapacidad_tipos[]" value="{{ $tipo }}" {{ $check(old('discapacidad_tipos', $rude->discapacidad_tipos), $tipo) ? 'checked' : '' }}> {{ $tipo }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Grado de discapacidad</label>
                    <select name="discapacidad_grado" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::GRADOS_DISCAPACIDAD as $grado)
                            <option value="{{ $grado }}" {{ old('discapacidad_grado', $rude->discapacidad_grado) === $grado ? 'selected' : '' }}>{{ $grado }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Dificultades en el aprendizaje</label>
                    <div class="grid grid-cols-1 gap-2 pt-2 text-sm">
                        @foreach($R::DIFICULTADES_APRENDIZAJE as $dif)
                            <label class="flex items-center gap-2"><input type="checkbox" name="dificultad_aprendizaje[]" value="{{ $dif }}" {{ $check(old('dificultad_aprendizaje', $rude->dificultad_aprendizaje), $dif) ? 'checked' : '' }}> {{ $dif }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección V: Convivencia -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-4">Sección V — La o el estudiante vive habitualmente con</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach($R::CONVIVENCIA as $i => $opc)
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="convive_con[]" value="{{ $opc }}" {{ $check(old('convive_con', $rude->convive_con), $opc) ? 'checked' : '' }}> {{ $i + 1 }}. {{ $opc }}</label>
                @endforeach
            </div>
            <p class="text-xs text-muted-foreground mt-4">Los datos de padre, madre y tutor(a) se gestionan desde el módulo de Padres/Tutores del estudiante y se incluyen en el export oficial.</p>
        </div>

        <!-- Estado -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-4">Estado del Trámite</h3>
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