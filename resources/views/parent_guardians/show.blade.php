@extends('layouts.app')
@section('titulo', $parentGuardian->apellidos . ', ' . $parentGuardian->nombres)

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
        $vinculosEdit = collect(old('students', []))->map(fn($v) => [
            'id'   => $v['id'] ?? null,
            'tipo' => $v['tipo_relacion'] ?? null,
        ])->values()->all();
        if (empty($vinculosEdit)) {
            $vinculosEdit = $parentGuardian->students->map(fn($s) => [
                'id'   => $s->id,
                'tipo' => $s->pivot->tipo_relacion,
            ])->values()->all();
        }
    @endphp
    <div class="mb-6">
        <a href="{{ route('tutores.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Volver al listado</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $parentGuardian->apellidos }}, {{ $parentGuardian->nombres }}</h1>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-editar-tutor" onclick="activarEdicionTutor()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </button>
                <button type="button" id="btn-cancelar-edicion-tutor" onclick="cancelarEdicionTutor()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div id="info-tutor">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos Personales</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Nombres</dt>
                            <dd class="text-foreground font-medium">{{ $parentGuardian->nombres }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Apellidos</dt>
                            <dd class="text-foreground font-medium">{{ $parentGuardian->apellidos }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Ocupación</dt>
                            <dd class="text-foreground">{{ $parentGuardian->ocupacion ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Estado</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$parentGuardian->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($parentGuardian->estado) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Documento de Identidad</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Tipo</dt>
                            <dd class="text-foreground">{{ $parentGuardian->tipo_documento }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Número</dt>
                            <dd class="text-foreground font-medium">{{ $parentGuardian->numero_documento }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Complemento</dt>
                            <dd class="text-foreground">{{ $parentGuardian->complemento ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Correo</dt>
                            <dd class="text-foreground">{{ $parentGuardian->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Teléfono</dt>
                            <dd class="text-foreground">{{ $parentGuardian->telefono ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-muted-foreground">Dirección</dt>
                            <dd class="text-foreground">{{ $parentGuardian->direccion ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Cuenta de Acceso</h3>
                    @if($parentGuardian->user)
                        <div class="mb-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">Con acceso al sistema</span>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Usuario</dt>
                                <dd class="text-foreground">{{ $parentGuardian->user->nombre }} {{ $parentGuardian->user->apellido }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Correo</dt>
                                <dd class="text-foreground">{{ $parentGuardian->user->email }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Estado</dt>
                                <dd>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$parentGuardian->user->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($parentGuardian->user->estado) }}</span>
                                </dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-muted-foreground">Sin cuenta de usuario (solo los tutores reciben acceso).</p>
                    @endif
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Estudiantes a cargo</h3>
                    @php
                        $vinculos = $parentGuardian->students->map(fn($s) => [
                            's'            => $s,
                            'tipo'         => $s->pivot->tipo_relacion,
                            'es_principal' => (bool) $s->pivot->es_principal,
                        ]);
                    @endphp
                    @if($vinculos->isEmpty())
                        <p class="text-sm text-muted-foreground">Sin estudiantes asignados.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($vinculos as $vinculo)
                                <li class="p-3 rounded-lg border {{ $vinculo['es_principal'] ? 'border-primary/30 bg-primary/5' : 'border-border' }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-foreground font-medium">{{ $vinculo['s']->apellidos }} {{ $vinculo['s']->nombres }}</span>
                                        <span class="flex items-center gap-2 shrink-0">
                                            @if($vinculo['es_principal'])
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-success/10 text-success">Principal</span>
                                            @endif
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $vinculo['tipo'] === 'TUTOR' ? 'bg-primary/10 text-primary' : 'bg-neutral-100 text-neutral-600' }}">{{ ucfirst(mb_strtolower($vinculo['tipo'])) }}</span>
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $parentGuardian->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $parentGuardian->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <form id="form-editar-tutor" method="POST" action="{{ route('tutores.update', $parentGuardian) }}" style="display:none;">
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
                            <input type="text" name="nombres" class="{{ $claseInput }}" value="{{ old('nombres', $parentGuardian->nombres) }}" required maxlength="150">
                            @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                            <input type="text" name="apellidos" class="{{ $claseInput }}" value="{{ old('apellidos', $parentGuardian->apellidos) }}" required maxlength="150">
                            @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Ocupación</label>
                            <input type="text" name="ocupacion" class="{{ $claseInput }}" value="{{ old('ocupacion', $parentGuardian->ocupacion) }}" maxlength="100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                            <select name="estado" class="{{ $claseInput }}">
                                @foreach(['ACTIVO','INACTIVO'] as $est)
                                    <option value="{{ $est }}" @selected(old('estado', $parentGuardian->estado) === $est)>{{ ucfirst(mb_strtolower($est)) }}</option>
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
                            <select name="tipo_documento" class="{{ $claseInput }}">
                                @foreach(['CI','PASAPORTE','OTRO'] as $td)
                                    <option value="{{ $td }}" @selected(old('tipo_documento', $parentGuardian->tipo_documento) === $td)>{{ $td }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                            <input type="text" name="numero_documento" class="{{ $claseInput }}" value="{{ old('numero_documento', $parentGuardian->numero_documento) }}" required maxlength="20">
                            @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                            <input type="text" name="complemento" class="{{ $claseInput }}" value="{{ old('complemento', $parentGuardian->complemento) }}" maxlength="5">
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Contacto</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                            <input type="text" name="telefono" class="{{ $claseInput }}" value="{{ old('telefono', $parentGuardian->telefono) }}" maxlength="20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo <span id="lbl-email-edit" class="text-error hidden">*</span></label>
                            <input type="email" name="email" id="email-edit" class="{{ $claseInput }}" value="{{ old('email', $parentGuardian->email) }}" maxlength="150" placeholder="Obligatorio si es tutor">
                            <p class="text-[11px] text-muted-foreground mt-1">El email es obligatorio cuando la persona es <strong>tutor</strong>, para otorgarle permisos de vista.</p>
                            @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                            <input type="text" name="direccion" class="{{ $claseInput }}" value="{{ old('direccion', $parentGuardian->direccion) }}" maxlength="255">
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider">Estudiantes a cargo</h3>
                        <button type="button" onclick="agregarFilaEstudiantes('edit')" class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-2 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar estudiante
                        </button>
                    </div>
                    <p class="text-xs text-muted-foreground mb-4">Seleccione los estudiantes y el tipo de relación. El <strong>tutor</strong> es quien recibe permisos de vista (requiere email).</p>
                    <div id="filas-estudiantes-edit"></div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $parentGuardian->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $parentGuardian->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-card border border-border rounded-xl p-6 flex items-center justify-end gap-3">
            <button type="button" onclick="cancelarEdicionTutor()" class="px-4 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border-none bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
        </div>
    </form>
@endsection

@push('scripts')
    @include('parent_guardians._estudiantes-js')
    <script>
        var vinculosEditTutor = @json($vinculosEdit);
        var filasEditCargadas = false;

        function cargarFilasEdicionTutor() {
            var cont = document.getElementById('filas-estudiantes-edit');
            cont.innerHTML = '';
            (vinculosEditTutor || []).forEach(function (v) {
                nuevaFilaEstudiantes('edit', { id: v.id, tipo: v.tipo });
            });
            reordenarFilasEstudiantes('edit');
            actualizarCorreoRequerido();
        }

        function activarEdicionTutor() {
            document.getElementById('info-tutor').style.display = 'none';
            document.getElementById('form-editar-tutor').style.display = 'block';
            document.getElementById('btn-editar-tutor').style.display = 'none';
            document.getElementById('btn-cancelar-edicion-tutor').style.display = 'inline-flex';
            if (!filasEditCargadas) {
                cargarFilasEdicionTutor();
                filasEditCargadas = true;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicionTutor() {
            document.getElementById('info-tutor').style.display = 'block';
            document.getElementById('form-editar-tutor').style.display = 'none';
            document.getElementById('btn-editar-tutor').style.display = 'inline-flex';
            document.getElementById('btn-cancelar-edicion-tutor').style.display = 'none';
        }

        @if($errors->any())
            activarEdicionTutor();
        @endif
    </script>
@endpush