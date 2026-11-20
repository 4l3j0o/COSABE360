@extends('layouts.app')

@section('titulo', 'RUDE Form I-V - ' . $student->codigo)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    @php
        $R = \App\Models\StudentRudeRecord::class;
        $check = fn($arr, $val) => is_array($arr) && in_array($val, $arr);
        $sino = fn($v) => $v === true || $v === 1 || $v === '1';
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            @include('layouts._volver', ['href' => route('rude.show', $student), 'texto' => 'Volver al RUDE'])
            <h1 class="text-xl font-bold text-foreground mt-2">RUDE — Formulario Oficial RM 0031/2025 (Secciones I-V)</h1>
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

        <!-- Sección I: Unidad Educativa (solo SIE según formulario oficial) -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección I — Datos de la Unidad Educativa</h3>
            <p class="text-xs text-muted-foreground mb-4">*) Código SIE de la Unidad Educativa. Fecha de llenado.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">*) Código SIE UE *</label>
                    <input type="text" name="codigo_sie_ue" value="{{ old('codigo_sie_ue', $rude->codigo_sie_ue) }}" required placeholder="8 dígitos" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Fecha de llenado *</label>
                    <input type="date" name="fecha_registro" value="{{ old('fecha_registro', $rude->fecha_registro?->format('Y-m-d')) }}" required class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
            </div>
        </div>

        <!-- Sección II: Estudiante -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección II — Datos de la o el Estudiante</h3>
            <p class="text-xs text-muted-foreground mb-4">CI 7-8 dígitos + complemento; certificado completo (si uno, todos); código RUDE solo lectura (SIE).</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.1 Apellido(s) y Nombre(s)</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-muted text-foreground text-sm font-medium">{{ $student->apellidos }} {{ $student->nombres }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.2 Lugar de nacimiento</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-muted text-foreground text-sm">{{ $student->lugar_nacimiento ?? '—' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-xs font-semibold text-foreground uppercase tracking-wide">2.3 Certificado de nacimiento <span class="text-muted-foreground normal-case font-normal">— Oficialía / Libro / Partida / Folio / Año</span></p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.3 Oficialía</label>
                    <input type="text" name="oficialia_nacimiento" value="{{ old('oficialia_nacimiento', $rude->oficialia_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.3 Libro</label>
                    <input type="text" name="libro_nacimiento" value="{{ old('libro_nacimiento', $rude->libro_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.3 Partida</label>
                    <input type="text" name="partida_nacimiento" value="{{ old('partida_nacimiento', $rude->partida_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.3 Folio</label>
                    <input type="text" name="folio_nacimiento" value="{{ old('folio_nacimiento', $rude->folio_nacimiento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.3 Año</label>
                    <input type="text" name="anio_nacimiento" value="{{ old('anio_nacimiento', $rude->anio_nacimiento) }}" placeholder="YYYY" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.4 Fecha de nacimiento</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-muted text-foreground text-sm">{{ $student->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.5 Tipo Documento</label>
                    <select name="tipo_documento" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::DOCUMENTOS as $val => $label)
                            <option value="{{ $val }}" {{ old('tipo_documento', $student->tipo_documento) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.5 CI / N.º Documento</label>
                    <input type="text" name="numero_documento" value="{{ old('numero_documento', $student->numero_documento) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.5 Complemento</label>
                    <input type="text" name="complemento" value="{{ old('complemento', $student->complemento) }}" placeholder="Ej: 1A, SC" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-medium text-muted-foreground">2.5.1 Solo extranjeros: tipo de documento presentado</label>
                    <select name="tipo_doc_extranjero" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::DOCUMENTOS_EXTRANJERO as $doc)
                            <option value="{{ $doc }}" {{ old('tipo_doc_extranjero', $rude->tipo_doc_extranjero) === $doc ? 'selected' : '' }}>{{ $doc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="text-xs font-medium text-muted-foreground">2.5.2 Código del documento (extranjeros)</label>
                    <input type="text" name="codigo_doc_extranjero" value="{{ old('codigo_doc_extranjero', $rude->codigo_doc_extranjero) }}" placeholder="Adjunte fotocopia" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.7 Sexo</label>
                    <p class="mt-1 px-3 py-2 rounded-lg bg-muted text-foreground text-sm">{{ $student->sexo === 'M' ? 'Masculino' : 'Femenino' }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.8 Discapacidad</label>
                    <input type="text" name="discapacidad" value="{{ old('discapacidad', $student->discapacidad) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">2.9 N.º Registro Discapacidad IBC</label>
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
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Departamento *</label>
                    <select name="dir_departamento" id="dir-departamento" required class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">Seleccionar…</option>
                        @foreach(array_keys(config('bolivia.departamentos')) as $depto)
                            <option value="{{ $depto }}" {{ old('dir_departamento', $rude->dir_departamento) === $depto ? 'selected' : '' }}>{{ $depto }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Provincia *</label>
                    <select name="dir_provincia" id="dir-provincia" required class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm" data-valor="{{ old('dir_provincia', $rude->dir_provincia) }}">
                        <option value="">Primero elija departamento…</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Municipio *</label>
                    <select name="dir_municipio" id="dir-municipio" required class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm" data-valor="{{ old('dir_municipio', $rude->dir_municipio) }}">
                        <option value="">Primero elija departamento…</option>
                    </select>
                </div>
                <div id="dir-municipio-otro-wrap" class="hidden">
                    <label class="text-xs font-medium text-muted-foreground">Otro municipio *</label>
                    <input type="text" name="dir_municipio_otro" value="{{ old('dir_municipio_otro') }}" maxlength="100" placeholder="Escriba el municipio" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
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
                    <label class="text-xs font-medium text-muted-foreground">4.1.1 Idioma en el que aprendió a hablar</label>
                    <select name="idioma_aprendio" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::IDIOMAS as $idioma)
                            <option value="{{ $idioma }}" {{ old('idioma_aprendio', $rude->idioma_aprendio) === $idioma ? 'selected' : '' }}>{{ $idioma }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-xs font-medium text-muted-foreground">4.1.2 Idioma(s) de uso frecuente <span class="text-muted-foreground/70">(marca los que usa)</span></span>
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1.5 mt-2">
                        @foreach($R::IDIOMAS as $idioma)
                            <label class="inline-flex items-center gap-2 text-sm text-foreground cursor-pointer">
                                <input type="checkbox" name="idiomas_uso_frecuente[]" value="{{ $idioma }}" {{ $check(old('idiomas_uso_frecuente', $rude->idiomas_uso_frecuente), $idioma) ? 'checked' : '' }} class="accent-primary">
                                {{ $idioma }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.1.3 Autoidentificación cultural</label>
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
                    <label class="text-xs font-medium text-muted-foreground">4.2.1 ¿Centro de Salud/Posta/Hospital en su comunidad?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="salud_centro_cercano" value="1" {{ $sino(old('salud_centro_cercano', $rude->salud_centro_cercano)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="salud_centro_cercano" value="0" {{ old('salud_centro_cercano', $rude->salud_centro_cercano) === 0 || $rude->salud_centro_cercano === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.2.2 ¿Acudió por problemas de salud el año pasado?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="salud_atendido_anio" value="1" {{ $sino(old('salud_atendido_anio', $rude->salud_atendido_anio)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="salud_atendido_anio" value="0" {{ old('salud_atendido_anio', $rude->salud_atendido_anio) === 0 || $rude->salud_atendido_anio === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.2.3 ¿Cuántas veces fue al Centro de Salud?</label>
                    <input type="number" name="salud_veces" min="0" max="365" value="{{ old('salud_veces', $rude->salud_veces) }}" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.2.4 ¿Tiene seguro de salud?</label>
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
                    <label class="text-xs font-medium text-muted-foreground">4.3.1 ¿Agua potable por cañería de red?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_agua_red" value="1" {{ $sino(old('serv_agua_red', $rude->serv_agua_red)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_agua_red" value="0" {{ old('serv_agua_red', $rude->serv_agua_red) === 0 || $rude->serv_agua_red === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.3.2 ¿Tiene baño en su vivienda?</label>
                    <select name="serv_bano" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::BANOS as $bano)
                            <option value="{{ $bano }}" {{ old('serv_bano', $rude->serv_bano) === $bano ? 'selected' : '' }}>{{ $bano }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.3.3 ¿Red de alcantarillado?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_alcantarillado" value="1" {{ $sino(old('serv_alcantarillado', $rude->serv_alcantarillado)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_alcantarillado" value="0" {{ old('serv_alcantarillado', $rude->serv_alcantarillado) === 0 || $rude->serv_alcantarillado === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.3.4 ¿Usa energía eléctrica para alumbrar?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_electricidad" value="1" {{ $sino(old('serv_electricidad', $rude->serv_electricidad)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_electricidad" value="0" {{ old('serv_electricidad', $rude->serv_electricidad) === 0 || $rude->serv_electricidad === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.3.5 ¿Servicio de recojo de basura?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="serv_recojo_basura" value="1" {{ $sino(old('serv_recojo_basura', $rude->serv_recojo_basura)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="serv_recojo_basura" value="0" {{ old('serv_recojo_basura', $rude->serv_recojo_basura) === 0 || $rude->serv_recojo_basura === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.3.6 La vivienda que ocupa el hogar es:</label>
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
                    <label class="text-xs font-medium text-muted-foreground">4.4.1 ¿Dónde accede a internet?</label>
                    <div class="grid grid-cols-2 gap-2 pt-2 text-sm">
                        @foreach(['Su vivienda', 'Lugares públicos', 'La Unidad Educativa', 'Teléfono celular', 'No necesita internet'] as $lugar)
                            <label class="flex items-center gap-2"><input type="checkbox" name="internet_lugares[]" value="{{ $lugar }}" {{ $check(old('internet_lugares', $rude->internet_lugares), $lugar) ? 'checked' : '' }}> {{ $lugar }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.4.2 ¿Con qué frecuencia usa internet?</label>
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
                    <label class="text-xs font-medium text-muted-foreground">4.5.1 ¿El estudiante trabajó?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_trabajo" value="1" {{ $sino(old('laboral_trabajo', $rude->laboral_trabajo)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_trabajo" value="0" {{ old('laboral_trabajo', $rude->laboral_trabajo) === 0 || $rude->laboral_trabajo === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.5.2 ¿En qué actividad trabajó?</label>
                    <select name="laboral_actividad" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::ACTIVIDADES_LABORALES as $act)
                            <option value="{{ $act }}" {{ old('laboral_actividad', $rude->laboral_actividad) === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.5.3 ¿En qué turno(s) trabajó?</label>
                    <div class="grid grid-cols-3 gap-2 pt-2 text-sm">
                        @foreach(['Mañana', 'Tarde', 'Noche'] as $turno)
                            <label class="flex items-center gap-1"><input type="checkbox" name="laboral_turnos[]" value="{{ $turno }}" {{ $check(old('laboral_turnos', $rude->laboral_turnos), $turno) ? 'checked' : '' }}> {{ $turno }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.5.4 ¿Con qué frecuencia trabajó?</label>
                    <select name="laboral_frecuencia" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::FRECUENCIAS_LABORAL as $frec)
                            <option value="{{ $frec }}" {{ old('laboral_frecuencia', $rude->laboral_frecuencia) === $frec ? 'selected' : '' }}>{{ $frec }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.5.5 ¿Recibió algún pago?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_recibio_pago" value="1" {{ $sino(old('laboral_recibio_pago', $rude->laboral_recibio_pago)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="laboral_recibio_pago" value="0" {{ old('laboral_recibio_pago', $rude->laboral_recibio_pago) === 0 || $rude->laboral_recibio_pago === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div class="sm:col-span-2 lg:col-span-5">
                    <label class="text-xs font-medium text-muted-foreground">4.5 Meses que trabajó</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 pt-2 text-sm">
                        @foreach($R::MESES as $mes)
                            <label class="flex items-center gap-1"><input type="checkbox" name="meses_trabajados[]" value="{{ $mes }}" {{ $check(old('meses_trabajados', $rude->meses_trabajados), $mes) ? 'checked' : '' }}> {{ $mes }}</label>
                        @endforeach
                    </div>
                </div>
            </div>

            <h4 class="text-xs font-semibold text-foreground uppercase tracking-wide mb-2">4.6 Medio de transporte</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.6.1 ¿Cómo llega a la UE?</label>
                    <select name="transporte_medio" id="transporte-medio" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::MEDIOS_TRANSPORTE as $medio)
                            <option value="{{ $medio }}" {{ old('transporte_medio', $rude->transporte_medio) === $medio ? 'selected' : '' }}>{{ $medio }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="transporte-otro-wrap" class="hidden">
                    <label class="text-xs font-medium text-muted-foreground">Otro medio (especifique) *</label>
                    <input type="text" name="transporte_otro" value="{{ old('transporte_otro', $rude->transporte_otro) }}" maxlength="150" placeholder="Ej: micro" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.6.2 Tiempo máximo de llegada</label>
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
                    <label class="text-xs font-medium text-muted-foreground">4.7.1 ¿Abandonó la UE el año pasado?</label>
                    <div class="flex gap-4 pt-2 text-sm">
                        <label class="flex items-center gap-1"><input type="radio" name="abandono_escolar" value="1" {{ $sino(old('abandono_escolar', $rude->abandono_escolar)) ? 'checked' : '' }}> Sí</label>
                        <label class="flex items-center gap-1"><input type="radio" name="abandono_escolar" value="0" {{ old('abandono_escolar', $rude->abandono_escolar) === 0 || $rude->abandono_escolar === false ? 'checked' : '' }}> No</label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.7.2 Razones del abandono escolar</label>
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
                    <label class="text-xs font-medium text-muted-foreground">4.8.1 Tipos de discapacidad</label>
                    <div class="grid grid-cols-2 gap-2 pt-2 text-sm">
                        @foreach($R::TIPOS_DISCAPACIDAD as $tipo)
                            <label class="flex items-center gap-2"><input type="checkbox" name="discapacidad_tipos[]" value="{{ $tipo }}" {{ $check(old('discapacidad_tipos', $rude->discapacidad_tipos), $tipo) ? 'checked' : '' }}> {{ $tipo }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.8.1 Grado de discapacidad</label>
                    <select name="discapacidad_grado" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">—</option>
                        @foreach($R::GRADOS_DISCAPACIDAD as $grado)
                            <option value="{{ $grado }}" {{ old('discapacidad_grado', $rude->discapacidad_grado) === $grado ? 'selected' : '' }}>{{ $grado }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">4.8.2 Dificultades en el aprendizaje</label>
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
            <div id="centro-acogida-wrap" class="hidden mt-3">
                <label class="text-xs font-medium text-muted-foreground">6. En tutela extraordinaria — Nombre del centro de acogida *</label>
                <input type="text" name="centro_acogida" value="{{ old('centro_acogida', $rude->centro_acogida) }}" maxlength="200" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <p class="text-xs text-muted-foreground mt-4">Si completa un bloque, el CI + nombres + apellidos son obligatorios. Los datos se guardan en Padres/Tutores del estudiante.</p>
        </div>

        <!-- Sección V: datos del padre, madre y tutor(a) -->
        <div class="bg-card border border-border rounded-xl p-5">
            <h3 class="font-semibold text-foreground text-sm mb-1">Sección V — Datos del Padre, Madre o Tutor(a)</h3>
            <p class="text-xs text-muted-foreground mb-4">Cédula/DNI, apellidos, nombres, ocupación, fecha de nacimiento, teléfono, idioma y grado de instrucción.</p>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                @foreach(['padre' => '5.2 Datos del Padre', 'madre' => '5.3 Datos de la Madre', 'tutor' => '5.4 Datos del Tutor(a)'] as $grupo => $titulo)
                @php $fam = $familiar[$grupo] ?? null; @endphp
                <div class="border border-border rounded-lg p-3">
                    <h4 class="font-bold text-foreground text-sm mb-2">{{ $titulo }}</h4>
                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2">
                            <input type="text" name="{{ $grupo }}_num_doc" value="{{ old($grupo.'_num_doc', $fam?->numero_documento) }}" placeholder="CI / DNI" maxlength="20" class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                            <input type="text" name="{{ $grupo }}_complemento" value="{{ old($grupo.'_complemento', $fam?->complemento) }}" placeholder="Comp." maxlength="5" class="w-20 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <input type="text" name="{{ $grupo }}_apellido_paterno" value="{{ old($grupo.'_apellido_paterno', $fam?->apellido_paterno ?? $fam?->apellidos) }}" placeholder="Apellido Paterno" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="{{ $grupo }}_apellido_materno" value="{{ old($grupo.'_apellido_materno', $fam?->apellido_materno) }}" placeholder="Apellido Materno" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="{{ $grupo }}_nombres" value="{{ old($grupo.'_nombres', $fam?->nombres) }}" placeholder="Nombre(s)" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="{{ $grupo }}_ocupacion" value="{{ old($grupo.'_ocupacion', $fam?->ocupacion) }}" placeholder="Ocupación laboral actual" maxlength="100" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="date" name="{{ $grupo }}_fecha_nacimiento" value="{{ old($grupo.'_fecha_nacimiento', $fam?->fecha_nacimiento?->format('Y-m-d')) }}" title="Fecha de nacimiento" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="{{ $grupo }}_telefono" value="{{ old($grupo.'_telefono', $fam?->telefono) }}" placeholder="Teléfono / Celular" maxlength="50" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <select name="{{ $grupo }}_idioma" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                            <option value="">Idioma que habla…</option>
                            @foreach($R::IDIOMAS as $idioma)
                                <option value="{{ $idioma }}" {{ old($grupo.'_idioma', $fam?->idioma) === $idioma ? 'selected' : '' }}>{{ $idioma }}</option>
                            @endforeach
                        </select>
                        <select name="{{ $grupo }}_grado_instruccion" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                            <option value="">Grado de instrucción…</option>
                            @foreach(\App\Models\ParentGuardian::GRADOS_INSTRUCCION as $grado)
                                <option value="{{ $grado }}" {{ old($grupo.'_grado_instruccion', $fam?->grado_instruccion) === $grado ? 'selected' : '' }}>{{ $grado }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach
                @php $famEx = ($familiar['tutor_extra'] ?? null); @endphp
                <div class="border border-border rounded-lg p-3">
                    <h4 class="font-bold text-foreground text-sm mb-2">5.5 Datos del Tutor(a) Extraordinario(a)</h4>
                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2">
                            <input type="text" name="tutor_extra_num_doc" value="{{ old('tutor_extra_num_doc', $famEx?->numero_documento) }}" placeholder="CI / DNI" maxlength="20" class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                            <input type="text" name="tutor_extra_complemento" value="{{ old('tutor_extra_complemento', $famEx?->complemento) }}" placeholder="Comp." maxlength="5" class="w-20 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <input type="text" name="tutor_extra_apellido_paterno" value="{{ old('tutor_extra_apellido_paterno', $famEx?->apellido_paterno) }}" placeholder="Apellido Paterno" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="tutor_extra_apellido_materno" value="{{ old('tutor_extra_apellido_materno', $famEx?->apellido_materno) }}" placeholder="Apellido Materno" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="tutor_extra_nombres" value="{{ old('tutor_extra_nombres', $famEx?->nombres) }}" placeholder="Nombre(s)" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="tutor_extra_ocupacion" value="{{ old('tutor_extra_ocupacion', $famEx?->ocupacion) }}" placeholder="Ocupación laboral actual" maxlength="100" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="tutor_extra_cargo_actual" value="{{ old('tutor_extra_cargo_actual', $famEx?->cargo_actual) }}" placeholder="Cargo actual" maxlength="150" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        <input type="text" name="tutor_extra_nombre_institucion" value="{{ old('tutor_extra_nombre_institucion', $famEx?->nombre_institucion) }}" placeholder="Nombre de la Institución" maxlength="200" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                </div>
            </div>
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

<script>
const RUDE_UBICACION = @json(config('bolivia.departamentos'));
function rudeLlenar(sel, opciones, placeholder, valor) {
    sel.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = placeholder;
    sel.appendChild(ph);
    opciones.forEach(function (o) {
        const opt = document.createElement('option');
        opt.value = o; opt.textContent = o;
        if (o === valor) opt.selected = true;
        sel.appendChild(opt);
    });
}
function rudeCascada(ids) {
    const selDepto = document.getElementById(ids.depto);
    const selProv = document.getElementById(ids.prov);
    const selMuni = document.getElementById(ids.muni);
    const otraWrap = document.getElementById(ids.otro);
    if (!selDepto || !selProv || !selMuni) return;
    function prov() {
        const d = RUDE_UBICACION[selDepto.value];
        rudeLlenar(selProv, d ? d.provincias : [], 'Seleccionar provincia…', selProv.dataset.valor || '');
        selProv.dataset.valor = '';
        muni();
    }
    function muni() {
        const d = RUDE_UBICACION[selDepto.value];
        const lista = (d ? d.municipios.slice() : []);
        lista.push('Otro');
        rudeLlenar(selMuni, lista, 'Seleccionar municipio…', selMuni.dataset.valor || '');
        selMuni.dataset.valor = '';
        if (otraWrap) otraWrap.classList.toggle('hidden', selMuni.value !== 'Otro');
    }
    selDepto.addEventListener('change', prov);
    selProv.addEventListener('change', muni);
    selMuni.addEventListener('change', function () {
        if (otraWrap) otraWrap.classList.toggle('hidden', selMuni.value !== 'Otro');
    });
    if (selDepto.value) prov();
}
document.addEventListener('DOMContentLoaded', function () {
    rudeCascada({ depto: 'dir-departamento', prov: 'dir-provincia', muni: 'dir-municipio', otro: 'dir-municipio-otro-wrap' });

    var selTrans = document.getElementById('transporte-medio');
    var otroTrans = document.getElementById('transporte-otro-wrap');
    function toggleTrans() { if (otroTrans) otroTrans.classList.toggle('hidden', !selTrans || selTrans.value !== 'Otro'); }
    if (selTrans) { selTrans.addEventListener('change', toggleTrans); toggleTrans(); }

    function toggleAcogida() {
        var wrap = document.getElementById('centro-acogida-wrap');
        if (!wrap) return;
        var marcada = false;
        document.querySelectorAll('input[name="convive_con[]"]').forEach(function (c) {
            if (c.checked && c.value === 'En tutela extraordinaria') marcada = true;
        });
        wrap.classList.toggle('hidden', !marcada);
    }
    document.querySelectorAll('input[name="convive_con[]"]').forEach(function (c) {
        c.addEventListener('change', toggleAcogida);
    });
    toggleAcogida();
});
</script>
@endsection