@extends('layouts.app')
@section('titulo', $student->apellidos . ', ' . $student->nombres)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @php
            $volver = request('volver') ? urldecode(request('volver')) : route('students.index');
            $volverTexto = request('volver') ? 'Volver a Estudiantes del Paralelo' : 'Volver al listado';
        @endphp
        @include('layouts._volver', ['href' => $volver, 'texto' => $volverTexto])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $student->apellidos }}, {{ $student->nombres }}</h1>
            <div class="flex items-center gap-2">
                <button type="button" onclick="abrirMatricula()" class="inline-flex items-center gap-2 bg-success text-success-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Matricular
                </button>
                <a href="{{ route('rude.show', $student) }}" class="inline-flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    RUDE
                </a>
                <a href="{{ route('boletin.show', $student) }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Boletín
                </a>
                <a href="{{ route('inclusion.show', $student) }}" class="inline-flex items-center gap-2 bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Inclusión
                </a>
                <a href="{{ route('expediente.show', $student) }}" class="inline-flex items-center gap-2 bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Expediente
                </a>
                    @if(auth()->user()->esAdmin())
                    <button type="button" id="btn-editar-estudiante" onclick="activarEdicion()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </button>
                    <button type="button" id="btn-cancelar-edicion" onclick="cancelarEdicion()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                        Cancelar
                    </button>
                    @endif
            </div>
        </div>
    </div>

    <div id="info-estudiante">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos Personales</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Código</dt>
                        <dd class="font-mono text-foreground font-medium">{{ $student->codigo }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Estado</dt>
                        <dd>
                            @php
                                $colores = ['ACTIVO' => 'bg-success/10 text-success', 'INACTIVO' => 'bg-warning/10 text-warning', 'EGRESADO' => 'bg-info/10 text-info', 'TRASLADADO' => 'bg-neutral-100 text-neutral-600', 'ELIMINADO' => 'bg-error/10 text-error'];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$student->estado] ?? '' }}">{{ mb_strtolower($student->estado) }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Nombres</dt>
                        <dd class="text-foreground">{{ $student->nombres }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Apellidos</dt>
                        <dd class="text-foreground">{{ $student->apellidos }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Fecha de Nacimiento</dt>
                        <dd class="text-foreground">{{ $student->fecha_nacimiento->format('d/m/Y') }} ({{ $student->fecha_nacimiento->age }} años)</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Lugar de Nacimiento</dt>
                        <dd class="text-foreground">{{ $student->lugar_nacimiento ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Sexo</dt>
                        <dd class="text-foreground">{{ $student->sexo === 'M' ? 'Masculino' : 'Femenino' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documento de Identidad</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Tipo</dt>
                        <dd class="text-foreground">{{ $student->tipo_documento }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Número</dt>
                        <dd class="text-foreground font-medium">{{ $student->numero_documento }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Complemento</dt>
                        <dd class="text-foreground">{{ $student->complemento ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto e Información Adicional</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Dirección</dt>
                        <dd class="text-foreground">{{ $student->direccion ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Teléfono</dt>
                        <dd class="text-foreground">{{ $student->telefono ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Correo</dt>
                        <dd class="text-foreground">{{ $student->email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Idioma</dt>
                        <dd class="text-foreground">{{ $student->idioma ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Cultura</dt>
                        <dd class="text-foreground">{{ $student->cultura ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Discapacidad</dt>
                        <dd class="text-foreground">{{ $student->discapacidad ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Inscripción y Matrícula</h3>

                @if(! $periodoActivo)
                    <p class="text-sm text-muted-foreground">No hay un período académico activo.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-border p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Inscripción</span>
                                @if($inscripcionPeriodo)
                                    @php
                                        $coloresIns = [
                                            'PENDIENTE' => 'bg-warning/10 text-warning',
                                            'COMPLETADA' => 'bg-success/10 text-success',
                                            'ANULADA' => 'bg-error/10 text-error',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresIns[$inscripcionPeriodo->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($inscripcionPeriodo->estado) }}</span>
                                @endif
                            </div>
                            @if($inscripcionPeriodo)
                                <dl class="space-y-1.5 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">Período</dt>
                                        <dd class="text-foreground">{{ $inscripcionPeriodo->gestion?->nombre ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">Fecha</dt>
                                        <dd class="text-foreground">{{ $inscripcionPeriodo->fecha?->format('d/m/Y') ?: '—' }}</dd>
                                    </div>
                                    @if($inscripcionPeriodo->observaciones)
                                        <div class="flex justify-between gap-2">
                                            <dt class="text-muted-foreground whitespace-nowrap">Observaciones</dt>
                                            <dd class="text-foreground text-right">{{ $inscripcionPeriodo->observaciones }}</dd>
                                        </div>
                                    @endif
                                </dl>
                                {{-- HU-59: verificar pago de preinscripción + contrato/anexo --}}
                                @if($inscripcionPeriodo->estado === 'PENDIENTE' && auth()->user()->hasAnyRole(['Administrador', 'Secretaría', 'Director']))
                                    <form method="POST" action="{{ route('inscripciones.verificar-pago', $inscripcionPeriodo) }}" onsubmit="return confirm('¿Verificar el pago de inscripción con el código {{ $student->codigo }}? El estudiante pasará a inscrito.')" class="mt-3">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">Verificar pago (contabilidad)</button>
                                    </form>
                                @endif
                                @if(auth()->user()->hasAnyRole(['Administrador', 'Secretaría', 'Director']))
                                    <div class="flex gap-2 mt-2">
                                        <a href="{{ route('inscripciones.contrato', $student) }}" class="flex-1 text-center px-3 py-1.5 border border-input rounded-lg text-xs bg-background text-foreground hover:bg-muted">Contrato PDF</a>
                                        <a href="{{ route('inscripciones.anexo', $student) }}" class="flex-1 text-center px-3 py-1.5 border border-input rounded-lg text-xs bg-background text-foreground hover:bg-muted">Anexo PDF</a>
                                    </div>
                                @endif
                            @else
                                <p class="text-sm text-muted-foreground">Sin inscripción en el período activo.</p>
                            @endif
                        </div>

                        <div class="rounded-lg border border-border p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Matrícula</span>
                                @if($matriculaPeriodo)
                                    @php
                                        $coloresMat = [
                                            'ACTIVA' => 'bg-success/10 text-success',
                                            'INACTIVA' => 'bg-neutral-100 text-neutral-600',
                                            'CANCELADA' => 'bg-error/10 text-error',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresMat[$matriculaPeriodo->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($matriculaPeriodo->estado) }}</span>
                                @endif
                            </div>
                            @if($matriculaPeriodo)
                                <dl class="space-y-1.5 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">Curso</dt>
                                        <dd class="text-foreground">{{ $matriculaPeriodo->section?->nombre ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">Fecha</dt>
                                        <dd class="text-foreground">{{ $matriculaPeriodo->fecha?->format('d/m/Y') ?: '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">Mensualidad</dt>
                                        <dd class="text-foreground">Bs {{ $matriculaPeriodo->monto_mensual }}</dd>
                                    </div>
                                    @if($matriculaPeriodo->observaciones)
                                        <div class="flex justify-between gap-2">
                                            <dt class="text-muted-foreground whitespace-nowrap">Observaciones</dt>
                                            <dd class="text-foreground text-right">{{ $matriculaPeriodo->observaciones }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @else
                                <p class="text-sm text-muted-foreground">Sin matrícula en el período activo.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Fotografía</h3>
                @if($student->fotografia)
                    <img src="{{ Storage::url($student->fotografia) }}" alt="{{ $student->nombres }}" class="w-full rounded-lg object-cover aspect-[3/4]">
                @else
                    <div class="w-full aspect-[3/4] bg-muted rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                @endif
            </div>

            <div class="bg-card border border-border rounded-xl p-6">
                <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Creado</dt>
                        <dd class="text-foreground">{{ $student->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Actualizado</dt>
                        <dd class="text-foreground">{{ $student->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @php
                $pgs = $student->parentGuardians;
                $etiquetas = [
                    'PADRE' => 'Padre', 'MADRE' => 'Madre', 'TIO' => 'Tío', 'TIA' => 'Tía',
                    'ABUELO' => 'Abuelo', 'ABUELA' => 'Abuela', 'HERMANO' => 'Hermano',
                    'HERMANA' => 'Hermana', 'OTRO' => 'Otro familiar', 'TUTOR' => 'Tutor',
                ];
            @endphp
            @if($pgs->isNotEmpty())
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Familia y Tutor</h3>
                    <ul class="space-y-2">
                        @foreach($pgs as $persona)
                            @php
                                $rol = $persona->pivot->tipo_relacion;
                                $esTutor = $persona->pivot->es_principal;
                                $etiqueta = $etiquetas[$rol] ?? $rol;
                            @endphp
                            <li class="p-3 rounded-lg border {{ $esTutor ? 'border-primary/30 bg-primary/5' : 'border-border' }}">
                                <div class="text-xs font-semibold uppercase tracking-wider {{ $esTutor ? 'text-primary' : 'text-muted-foreground' }}">{{ $etiqueta }}{{ $esTutor ? ' · Tutor' : '' }}</div>
                                <div class="text-foreground font-medium">{{ $persona->nombres }} {{ $persona->apellidos }}</div>
                                <div class="text-muted-foreground text-xs">{{ $persona->numero_documento ? 'CI ' . $persona->numero_documento : '' }}{{ $esTutor && $persona->email ? ' · ' . $persona->email : '' }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    </div>

    <form id="form-editar-estudiante" method="POST" enctype="multipart/form-data" action="{{ route('students.update', $student) }}" style="display:none;">
        @csrf @method('PUT')
        @php
            $claseInput = 'w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary';
        @endphp

        @if($errors->any())
            <div class="mb-4 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos Personales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                            <input type="text" name="nombres" class="{{ $claseInput }}" value="{{ old('nombres', $student->nombres) }}" required>
                            @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                            <input type="text" name="apellidos" class="{{ $claseInput }}" value="{{ old('apellidos', $student->apellidos) }}" required>
                            @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de Nacimiento <span class="text-error">*</span></label>
                            <input type="date" name="fecha_nacimiento" class="{{ $claseInput }}" value="{{ old('fecha_nacimiento', $student->fecha_nacimiento->format('Y-m-d')) }}" required>
                            @error('fecha_nacimiento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar de Nacimiento</label>
                            <input type="text" name="lugar_nacimiento" class="{{ $claseInput }}" value="{{ old('lugar_nacimiento', $student->lugar_nacimiento) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sexo <span class="text-error">*</span></label>
                            <select name="sexo" class="{{ $claseInput }}" required>
                                <option value="M" {{ old('sexo', $student->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $student->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                            <select name="estado" class="{{ $claseInput }}" required>
                                @foreach(['ACTIVO','INACTIVO','EGRESADO','TRASLADADO'] as $est)
                                    <option value="{{ $est }}" {{ old('estado', $student->estado) === $est ? 'selected' : '' }}>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $est))) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documento de Identidad</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                            <select name="tipo_documento" class="{{ $claseInput }}" required>
                                @foreach(['CI','PASAPORTE','OTRO'] as $td)
                                    <option value="{{ $td }}" {{ old('tipo_documento', $student->tipo_documento) === $td ? 'selected' : '' }}>{{ $td }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                            <input type="text" name="numero_documento" class="{{ $claseInput }}" value="{{ old('numero_documento', $student->numero_documento) }}" required>
                            @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                            <input type="text" name="complemento" class="{{ $claseInput }}" value="{{ old('complemento', $student->complemento) }}" maxlength="5">
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto e Información Adicional</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                            <input type="text" name="direccion" class="{{ $claseInput }}" value="{{ old('direccion', $student->direccion) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                            <input type="text" name="telefono" class="{{ $claseInput }}" value="{{ old('telefono', $student->telefono) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del Estudiante</label>
                            <input type="email" name="email_estudiante" class="{{ $claseInput }}" value="{{ old('email_estudiante', $student->email) }}">
                            <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener el actual.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Idioma</label>
                            <input type="text" name="idioma" class="{{ $claseInput }}" value="{{ old('idioma', $student->idioma) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Cultura</label>
                            <input type="text" name="cultura" class="{{ $claseInput }}" value="{{ old('cultura', $student->cultura) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Discapacidad</label>
                            <input type="text" name="discapacidad" class="{{ $claseInput }}" value="{{ old('discapacidad', $student->discapacidad) }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fotografía</label>
                            <input type="file" name="fotografia" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener la actual.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    @php
                        $coloresInsEdit = ['PENDIENTE' => 'bg-warning/10 text-warning', 'COMPLETADA' => 'bg-success/10 text-success', 'ANULADA' => 'bg-error/10 text-error'];
                        $coloresMatEdit = ['ACTIVA' => 'bg-success/10 text-success', 'INACTIVA' => 'bg-neutral-100 text-neutral-600', 'CANCELADA' => 'bg-error/10 text-error'];
                    @endphp
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Inscripción y Matrícula</h3>

                    @if(! $periodoActivo)
                        <p class="text-sm text-muted-foreground">No hay un período académico activo.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="rounded-lg border border-border p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Inscripción</span>
                                    @if($inscripcionPeriodo)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresInsEdit[$inscripcionPeriodo->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($inscripcionPeriodo->estado) }}</span>
                                    @endif
                                </div>
                                @if($inscripcionPeriodo)
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                                            <select name="inscripcion_estado" class="{{ $claseInput }}">
                                                @foreach(\App\Models\Enrollment::ESTADOS as $est)
                                                    <option value="{{ $est }}" {{ old('inscripcion_estado', $inscripcionPeriodo->estado) === $est ? 'selected' : '' }}>{{ ucfirst(mb_strtolower($est)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha</label>
                                            <input type="date" name="inscripcion_fecha" class="{{ $claseInput }}" value="{{ old('inscripcion_fecha', $inscripcionPeriodo->fecha?->format('Y-m-d')) }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                                            <textarea name="inscripcion_observaciones" rows="2" class="{{ $claseInput }}">{{ old('inscripcion_observaciones', $inscripcionPeriodo->observaciones) }}</textarea>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-muted-foreground">Sin inscripción en el período activo. Inscríbalo desde el listado pulsando "sin inscribir".</p>
                                @endif
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Matrícula</span>
                                    @if($matriculaPeriodo)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresMatEdit[$matriculaPeriodo->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($matriculaPeriodo->estado) }}</span>
                                    @endif
                                </div>
                                @if($matriculaPeriodo)
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                                            <select name="matricula_estado" class="{{ $claseInput }}">
                                                @foreach(\App\Models\Registration::ESTADOS as $est)
                                                    <option value="{{ $est }}" {{ old('matricula_estado', $matriculaPeriodo->estado) === $est ? 'selected' : '' }}>{{ ucfirst(mb_strtolower($est)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha</label>
                                            <input type="date" name="matricula_fecha" class="{{ $claseInput }}" value="{{ old('matricula_fecha', $matriculaPeriodo->fecha?->format('Y-m-d')) }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Mensualidad (Bs)</label>
                                            <input type="number" step="0.01" min="0" name="matricula_monto" class="{{ $claseInput }}" value="{{ old('matricula_monto', $matriculaPeriodo->monto_mensual) }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                                            <textarea name="matricula_observaciones" rows="2" class="{{ $claseInput }}">{{ old('matricula_observaciones', $matriculaPeriodo->observaciones) }}</textarea>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-muted-foreground">Sin matrícula en el período activo. Use el botón "Matricular".</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider">Familia y Tutor <span class="text-error">*</span></h3>
                        <button type="button" onclick="agregarFilaFamiliarEdit()" class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-2 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar familiar
                        </button>
                    </div>
                    <p class="text-xs text-muted-foreground mb-4">Debe haber exactamente un <strong>tutor</strong>. Use la relación <strong>TUTOR</strong> si no es de la familia.</p>

                    <div id="lista-familiares-edit" style="display:none;"></div>
                    <div id="sin-familiar-edit" class="text-xs text-muted-foreground bg-muted/40 border border-dashed border-border rounded-lg px-4 py-5 text-center">
                        No hay familiares registrados. Use <strong>"Agregar familiar"</strong>.
                    </div>
                    @error('familiares') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 bg-card border border-border rounded-xl p-6 flex items-center justify-end gap-3">
            <button type="button" onclick="cancelarEdicion()" class="px-4 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border-none bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
        </div>
    </form>

    <div class="mt-6 bg-card border border-border rounded-xl p-6">
        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documentación</h3>

        @if($documentos->isEmpty())
            <p class="text-sm text-muted-foreground">Este estudiante no tiene documentos registrados en su expediente.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Tipo de Documento</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Estado</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Fecha de entrega</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-foreground">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentos as $doc)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-2.5 font-medium text-foreground">{{ $doc->tipo_documento }}</td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $colores = [
                                            'PENDIENTE' => 'bg-warning/10 text-warning',
                                            'ENTREGADO' => 'bg-success/10 text-success',
                                            'VERIFICADO' => 'bg-info/10 text-info',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$doc->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($doc->estado) }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-muted-foreground text-xs">{{ $doc->fecha_entrega ? $doc->fecha_entrega->format('d/m/Y') : '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <form method="POST" action="{{ route('student_documents.updateStatus', $doc) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="{{ $doc->estado === 'PENDIENTE' ? 'ENTREGADO' : 'PENDIENTE' }}">
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="{{ $doc->estado === 'PENDIENTE' ? 'Marcar como entregado' : 'Marcar como pendiente' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6 bg-card border border-border rounded-xl p-6">
        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Historial de Cambios</h3>

        @if($historial->isEmpty())
            <p class="text-sm text-muted-foreground">No se registraron cambios para este estudiante.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Fecha</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Usuario</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Acción</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-foreground">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $registro)
                            <tr class="border-b border-border last:border-b-0">
                                <td class="px-4 py-2.5 text-muted-foreground whitespace-nowrap">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2.5 text-foreground">
                                    @if($registro->user)
                                        {{ $registro->user->nombre }} {{ $registro->user->apellido }}
                                    @else
                                        <span class="text-muted-foreground">Sistema</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $coloresBtn = ['EDITAR' => 'bg-primary/10 text-primary', 'CREAR' => 'bg-success/10 text-success', 'ELIMINAR' => 'bg-error/10 text-error'];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresBtn[$registro->accion] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($registro->accion) }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $registro->detalle }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- MODAL MATRICULA --}}
    <div id="modal-matricula" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:36rem; max-height:calc(100vh - 4rem); display:flex; flex-direction:column; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Matricular Estudiante</h2>
                    <button onclick="closeModalMatricula()" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div style="flex:1; overflow-y:auto; padding:1.5rem;">
                    @if(! $periodoActivo)
                        <p class="text-sm text-muted-foreground">No hay un período académico activo. Crea o abre un período para poder matricular estudiantes.</p>
                    @elseif($matriculaPeriodo && $matriculaPeriodo->estado === 'ACTIVA')
                        <div class="bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Este estudiante ya se encuentra matriculado en el período académico activo.
                        </div>
                    @elseif(! $inscripcionPeriodo || $inscripcionPeriodo->estado !== 'COMPLETADA')
                        <p class="text-sm text-muted-foreground">Este estudiante no tiene una inscripción <strong>completada</strong> en el período activo. Complete la inscripción desde <strong>Editar</strong> para poder matricularlo.</p>
                    @else
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-5">
                            <div>
                                <dt class="text-muted-foreground">Estudiante</dt>
                                <dd class="text-foreground font-medium">{{ $student->apellidos }} {{ $student->nombres }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Código</dt>
                                <dd class="text-foreground font-mono font-medium">{{ $student->codigo }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Período académico</dt>
                                <dd class="text-foreground font-medium">{{ $periodoActivo->nombre }}</dd>
                            </div>
                        </dl>
                        <form method="POST" action="{{ route('estudiantes.matricular', $student) }}" class="space-y-4">
                            @csrf
                            <div>
                                @php $cursoSeleccionadoBuscador = $cursos->firstWhere('id', old('section_id')); @endphp
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso (grado y paralelo) <span class="text-error">*</span></label>
                                <input type="hidden" name="section_id" id="mat-section_id" value="{{ old('section_id') }}">
                                <div id="mat-curso-picker" style="position:relative;">
                                    <input type="text" id="mat-curso-picker-input" placeholder="Buscar curso (grado, paralelo o nombre)..." autocomplete="off" value="{{ $cursoSeleccionadoBuscador?->nombre ?? '' }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <div id="mat-curso-picker-lista" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:60; max-height:240px; overflow-y:auto; background:var(--card); border:1px solid var(--border); border-radius:0.5rem; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15);">
                                        @foreach($cursos as $curso)
                                            <div data-curso-id="{{ $curso->id }}" data-curso-label="{{ $curso->nombre }}" data-curso-busqueda="@php echo mb_strtolower(trim(($curso->grade?->nombre ?? '') . ' ' . ($curso->section?->nombre ?? '') . ' ' . ' paralelo ' . $curso->nombre)); @endphp" onmousedown="matSeleccionarCurso(this); return false;" style="padding:0.625rem 0.75rem; cursor:pointer; font-size:0.875rem; color:var(--foreground); border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background=''">
                                                {{ $curso->nombre }}
                                            </div>
                                        @endforeach
                                        @if($cursos->isEmpty())
                                            <div style="padding:0.625rem 0.75rem; font-size:0.875rem; color:var(--muted-foreground);">No hay cursos activos en el período.</div>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">Escriba para filtrar por grado, paralelo o nombre.</p>
                                @error('section_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de matrícula <span class="text-error">*</span></label>
                                <input type="date" name="fecha" id="mat-fecha" value="{{ old('fecha', date('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-muted/40 border border-border rounded-lg px-4 py-3 text-sm">
                                <p class="text-muted-foreground">
                                    <span class="font-medium text-foreground">Mensualidad:</span>
                                    <span class="font-semibold text-foreground">Bs {{ number_format($montoMensual, 2) }}</span>
                                    <span class="text-muted-foreground"> (establecida en la configuración del colegio, 10 cuotas de febrero a noviembre)</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                                <textarea name="observaciones" rows="2" placeholder="Opcional..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('observaciones') }}</textarea>
                                @error('observaciones') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            @if($errors->has('general'))
                                <div class="bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm">
                                    {{ $errors->first('general') }}
                                </div>
                            @endif
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                                <button type="button" onclick="closeModalMatricula()" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                                <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar matrícula</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $familiasInicialesArr = old('familiares', $student->parentGuardians->map(function ($p) {
            return [
                'id_existente' => $p->id,
                'nombres' => $p->nombres,
                'apellidos' => $p->apellidos,
                'numero_documento' => $p->numero_documento,
                'tipo_documento' => $p->tipo_documento,
                'complemento' => $p->complemento,
                'telefono' => $p->telefono,
                'email' => $p->email,
                'direccion' => $p->direccion,
                'ocupacion' => $p->ocupacion,
                'relacion' => $p->pivot->tipo_relacion ?? null,
                'es_tutor' => $p->pivot->es_principal ? 1 : 0,
            ];
        })->all());
    @endphp
    <script>
        var estudiantesMap = {
            {{ $student->id }}: @json($student->toArray())
        };

        var familiasIniciales = @json($familiasInicialesArr);

        function cargarFamiliaresEdicion() {
            var cont = document.getElementById('lista-familiares-edit');
            cont.innerHTML = '';
            familiarSeqEdit = 0;
            (familiasIniciales || []).forEach(function(f) {
                cont.insertAdjacentHTML('beforeend', construirFilaFamiliar(familiarSeqEdit, f, true));
                familiarSeqEdit++;
            });
            renumerarFilas('lista-familiares-edit');
            if (familiasIniciales && familiasIniciales.length) {
                cont.style.display = 'block';
                var sn = document.getElementById('sin-familiar-edit');
                if (sn) sn.style.display = 'none';
            }
        }

        function activarEdicion() {
            document.getElementById('info-estudiante').style.display = 'none';
            document.getElementById('form-editar-estudiante').style.display = 'block';
            document.getElementById('btn-editar-estudiante').style.display = 'none';
            document.getElementById('btn-cancelar-edicion').style.display = 'inline-flex';
            var cont = document.getElementById('lista-familiares-edit');
            if (!cont.dataset.cargado) {
                cargarFamiliaresEdicion();
                cont.dataset.cargado = '1';
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicion() {
            document.getElementById('info-estudiante').style.display = 'block';
            document.getElementById('form-editar-estudiante').style.display = 'none';
            document.getElementById('btn-editar-estudiante').style.display = 'inline-flex';
            document.getElementById('btn-cancelar-edicion').style.display = 'none';
        }

        function abrirMatricula() {
            var fecha = document.getElementById('mat-fecha');
            if (fecha && !fecha.value) {
                var hoy = new Date();
                fecha.value = hoy.getFullYear() + '-' + String(hoy.getMonth() + 1).padStart(2, '0') + '-' + String(hoy.getDate()).padStart(2, '0');
            }
            var m = document.getElementById('modal-matricula');
            if (m) { m.style.display = 'block'; document.body.style.overflow = 'hidden'; }
        }

        function closeModalMatricula() {
            var m = document.getElementById('modal-matricula');
            if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
        }

        function matBuscarCursos() {
            var input = document.getElementById('mat-curso-picker-input');
            var lista = document.getElementById('mat-curso-picker-lista');
            if (!input || !lista || !lista.style) return;
            var q = input.value.trim().toLowerCase();
            var items = lista.querySelectorAll('[data-curso-id]');
            var vistos = 0;
            items.forEach(function (it) {
                var coincide = !q || it.getAttribute('data-curso-busqueda').indexOf(q) !== -1;
                if (!q) it.style.display = vistos < 5 ? '' : 'none';
                else it.style.display = coincide ? '' : 'none';
                if (coincide) vistos++;
            });
            lista.style.display = 'block';
        }

        function matSeleccionarCurso(el) {
            document.getElementById('mat-section_id').value = el.getAttribute('data-curso-id');
            document.getElementById('mat-curso-picker-input').value = el.getAttribute('data-curso-label');
            document.getElementById('mat-curso-picker-lista').style.display = 'none';
        }

        function matCerrarListaCurso(e) {
            var p = document.getElementById('mat-curso-picker');
            var lista = document.getElementById('mat-curso-picker-lista');
            if (!p || !lista) return;
            if (!p.contains(e.target)) lista.style.display = 'none';
        }

        var matPickerInput = document.getElementById('mat-curso-picker-input');
        var matPickerLista = document.getElementById('mat-curso-picker-lista');
        if (matPickerInput && matPickerLista) {
            matPickerInput.addEventListener('focus', function () {
                matBuscarCursos();
            });
            matPickerInput.addEventListener('input', matBuscarCursos);
            matPickerInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    matPickerLista.style.display = 'none';
                } else if (e.key === 'Enter') {
                    var items = matPickerLista.querySelectorAll('[data-curso-id]');
                    for (var i = 0; i < items.length; i++) {
                        if (items[i].style.display !== 'none') { matSeleccionarCurso(items[i]); e.preventDefault(); break; }
                    }
                }
            });
            document.addEventListener('click', matCerrarListaCurso);
        }

        @if(old('section_id') && $errors->any())
            abrirMatricula();
        @elseif($errors->any())
            activarEdicion();
        @endif
        @if(request('matricula'))
            abrirMatricula();
        @endif
    </script>
@endpush

@include('students.partials._familiares-js')
