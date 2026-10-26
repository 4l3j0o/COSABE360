@extends('layouts.app')
@section('titulo', $user->nombre . ' ' . $user->apellido)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    @php
        $coloresEstado = [
            'ACTIVO'    => 'bg-success/10 text-success',
            'INACTIVO'  => 'bg-warning/10 text-warning',
            'ELIMINADO' => 'bg-error/10 text-error',
        ];
        $coloresRol = [
            'Administrador' => 'bg-primary/10 text-primary',
            'Director'      => 'bg-info/10 text-info',
            'Secretaría'    => 'bg-warning/10 text-warning',
            'Docente'       => 'bg-success/10 text-success',
            'Estudiante'    => 'bg-info/10 text-info',
        ];
        $rolNombre = $user->rol?->nombre ?? '—';
    @endphp
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('users.index'), 'texto' => 'Volver al listado'])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $user->nombre }} {{ $user->apellido }}</h1>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-editar-usuario" onclick="activarEdicionUsuario()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </button>
                <button type="button" id="btn-cancelar-edicion-usuario" onclick="cancelarEdicionUsuario()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <div id="info-usuario">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos de la Cuenta</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Nombre</dt>
                            <dd class="text-foreground font-medium">{{ $user->nombre }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Apellido</dt>
                            <dd class="text-foreground font-medium">{{ $user->apellido }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Correo</dt>
                            <dd class="text-foreground">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Teléfono</dt>
                            <dd class="text-foreground">{{ $user->telefono ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Rol</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresRol[$rolNombre] ?? 'bg-muted text-muted-foreground' }}">{{ $rolNombre }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Estado</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$user->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($user->estado) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro de Acceso</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $user->created_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Último acceso</dt>
                            <dd class="text-foreground">{{ $user->ultimo_acceso?->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Intentos fallidos</dt>
                            <dd class="text-foreground">{{ $user->intentos_fallidos ?? 0 }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Bloqueado hasta</dt>
                            <dd class="text-foreground">{{ $user->bloqueado_hasta?->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $user->updated_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <form id="form-editar-usuario" method="POST" action="{{ route('users.update', $user) }}" style="display:none;">
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
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre <span class="text-error">*</span></label>
                            <input name="nombre" type="text" value="{{ old('nombre', $user->nombre) }}" required class="{{ $claseInput }}">
                            @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellido <span class="text-error">*</span></label>
                            <input name="apellido" type="text" value="{{ old('apellido', $user->apellido) }}" required class="{{ $claseInput }}">
                            @error('apellido') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Cuenta de Acceso</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo electrónico <span class="text-error">*</span></label>
                            <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="{{ $claseInput }}">
                            @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        @if(strtolower($user->email) === strtolower(config('app.emergency_admin_email')))
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Contraseña <span class="text-muted-foreground font-normal">(vacío = mantener, solo emergencia)</span></label>
                            <input name="password" type="password" class="{{ $claseInput }}">
                            @error('password') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Rol y Contacto</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Rol <span class="text-error">*</span></label>
                            <select name="role" class="{{ $claseInput }}">
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected((string)old('role', $user->role) === (string)$rol->id)>{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                            <input name="telefono" type="tel" value="{{ old('telefono', $user->telefono) }}" class="{{ $claseInput }}">
                            @error('telefono') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Estado</h3>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$user->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($user->estado) }}</span>
                        <span class="text-xs text-muted-foreground">Se activa/desactiva desde el listado de usuarios.</span>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro de Acceso</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Último acceso</dt>
                            <dd class="text-foreground">{{ $user->ultimo_acceso?->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $user->updated_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-card border border-border rounded-xl p-6 flex items-center justify-end gap-3">
            <button type="button" onclick="cancelarEdicionUsuario()" class="px-4 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border-none bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function activarEdicionUsuario() {
            document.getElementById('info-usuario').style.display = 'none';
            document.getElementById('form-editar-usuario').style.display = 'block';
            document.getElementById('btn-editar-usuario').style.display = 'none';
            document.getElementById('btn-cancelar-edicion-usuario').style.display = 'inline-flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicionUsuario() {
            document.getElementById('info-usuario').style.display = 'block';
            document.getElementById('form-editar-usuario').style.display = 'none';
            document.getElementById('btn-editar-usuario').style.display = 'inline-flex';
            document.getElementById('btn-cancelar-edicion-usuario').style.display = 'none';
        }

        @if($errors->any())
            activarEdicionUsuario();
        @endif
    </script>
@endpush
