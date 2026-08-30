@extends('layouts.app')
@section('titulo', $student->apellidos . ', ' . $student->nombres)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al listado</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $student->apellidos }}, {{ $student->nombres }}</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('estudiantes.matriculacion', $student) }}" class="inline-flex items-center gap-2 bg-success text-success-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Matricular
                </a>
                <button type="button" id="btn-editar-estudiante" onclick="activarEdicion()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </button>
                <button type="button" id="btn-cancelar-edicion" onclick="cancelarEdicion()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    Cancelar
                </button>
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
                                        <dd class="text-foreground">{{ $matriculaPeriodo->course?->nombre ?? '—' }}</dd>
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

        @if($errors->any())
            activarEdicion();
        @endif
    </script>
@endpush

@include('students.partials._familiares-js')
