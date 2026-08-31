@extends('layouts.app')
@section('titulo', $teacher->apellidos . ', ' . $teacher->nombres)

@section('sidebar')
    @if(auth()->user()->hasRole('Director'))
        @include('dashboard.partials._sidebar-director')
    @else
        @include('dashboard.partials._sidebar-admin')
    @endif
@endsection

@section('contenido')
    @php
        $coloresEstado = [
            'ACTIVO'    => 'bg-success/10 text-success',
            'INACTIVO'  => 'bg-warning/10 text-warning',
            'ELIMINADO' => 'bg-error/10 text-error',
        ];
    @endphp
    <div class="mb-6">
        <a href="{{ route('teachers.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al listado</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $teacher->apellidos }}, {{ $teacher->nombres }}</h1>
            <div class="flex items-center gap-2">
                @if(auth()->user()->esAdmin())
                <button type="button" id="btn-editar-docente" onclick="activarEdicionDocente()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </button>
                <button type="button" id="btn-cancelar-edicion-docente" onclick="cancelarEdicionDocente()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    Cancelar
                </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div id="info-docente">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos Personales</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Código</dt>
                            <dd class="font-mono text-xs text-muted-foreground">{{ $teacher->codigo }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Nombres</dt>
                            <dd class="text-foreground font-medium">{{ $teacher->nombres }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Apellidos</dt>
                            <dd class="text-foreground font-medium">{{ $teacher->apellidos }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Especialidad</dt>
                            <dd class="text-foreground">{{ $teacher->especialidad ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Estado</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$teacher->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($teacher->estado) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documento de Identidad</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Tipo</dt>
                            <dd class="text-foreground">{{ $teacher->tipo_documento }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Número</dt>
                            <dd class="text-foreground font-medium">{{ $teacher->numero_documento }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Complemento</dt>
                            <dd class="text-foreground">{{ $teacher->complemento ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Correo</dt>
                            <dd class="text-foreground">{{ $teacher->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Teléfono</dt>
                            <dd class="text-foreground">{{ $teacher->telefono ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Cuenta de Acceso</h3>
                    @if($teacher->user)
                        <div class="mb-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">Con acceso al sistema</span>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Usuario</dt>
                                <dd class="text-foreground">{{ $teacher->user->nombre }} {{ $teacher->user->apellido }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Rol</dt>
                                <dd class="text-foreground">{{ $teacher->user->rol?->nombre ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Correo</dt>
                                <dd class="text-foreground">{{ $teacher->user->email }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Estado</dt>
                                <dd>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$teacher->user->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($teacher->user->estado) }}</span>
                                </dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-muted-foreground">Sin cuenta de usuario (se crea automáticamente al asignar un correo).</p>
                    @endif
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $teacher->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $teacher->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <form id="form-editar-docente" method="POST" action="{{ route('teachers.update', $teacher) }}" style="display:none;">
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
                            <input type="text" name="nombres" class="{{ $claseInput }}" value="{{ old('nombres', $teacher->nombres) }}" required maxlength="150">
                            @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                            <input type="text" name="apellidos" class="{{ $claseInput }}" value="{{ old('apellidos', $teacher->apellidos) }}" required maxlength="150">
                            @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Especialidad</label>
                            <input type="text" name="especialidad" class="{{ $claseInput }}" value="{{ old('especialidad', $teacher->especialidad) }}" maxlength="150" placeholder="Ej: Matemáticas">
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documento de Identidad</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                            <select name="tipo_documento" class="{{ $claseInput }}">
                                @foreach(['CI','PASAPORTE','OTRO'] as $td)
                                    <option value="{{ $td }}" @selected(old('tipo_documento', $teacher->tipo_documento) === $td)>{{ $td }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                            <input type="text" name="numero_documento" class="{{ $claseInput }}" value="{{ old('numero_documento', $teacher->numero_documento) }}" required maxlength="20">
                            @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                            <input type="text" name="complemento" class="{{ $claseInput }}" value="{{ old('complemento', $teacher->complemento) }}" maxlength="5">
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                            <input type="text" name="telefono" class="{{ $claseInput }}" value="{{ old('telefono', $teacher->telefono) }}" maxlength="20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo Electrónico</label>
                            <input type="email" name="email" class="{{ $claseInput }}" value="{{ old('email', $teacher->email) }}" maxlength="150">
                            <p class="text-[11px] text-muted-foreground mt-1">Si se asigna un correo se crea (o se vincula) la cuenta de usuario docente con contraseña por defecto <strong>password123</strong>.</p>
                            @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Estado</h3>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$teacher->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($teacher->estado) }}</span>
                        <span class="text-xs text-muted-foreground">Se activa/desactiva desde el listado de docentes.</span>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Cuenta de Acceso</h3>
                    @if($teacher->user)
                        <div class="mb-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">Con acceso al sistema</span>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Usuario</dt>
                                <dd class="text-foreground">{{ $teacher->user->nombre }} {{ $teacher->user->apellido }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Correo</dt>
                                <dd class="text-foreground">{{ $teacher->user->email }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-muted-foreground">Sin cuenta de usuario (se crea al asignar un correo).</p>
                    @endif
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $teacher->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $teacher->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-card border border-border rounded-xl p-6 flex items-center justify-end gap-3">
            <button type="button" onclick="cancelarEdicionDocente()" class="px-4 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border-none bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function activarEdicionDocente() {
            document.getElementById('info-docente').style.display = 'none';
            document.getElementById('form-editar-docente').style.display = 'block';
            document.getElementById('btn-editar-docente').style.display = 'none';
            document.getElementById('btn-cancelar-edicion-docente').style.display = 'inline-flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicionDocente() {
            document.getElementById('info-docente').style.display = 'block';
            document.getElementById('form-editar-docente').style.display = 'none';
            document.getElementById('btn-editar-docente').style.display = 'inline-flex';
            document.getElementById('btn-cancelar-edicion-docente').style.display = 'none';
        }

        @if($errors->any())
            activarEdicionDocente();
        @endif
    </script>
@endpush