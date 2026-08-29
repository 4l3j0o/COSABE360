@extends('layouts.app')
@section('titulo', 'Tutores')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Tutores</h1>
            <p class="text-sm text-muted-foreground">Tutores con acceso al sistema y los estudiantes a su cargo</p>
        </div>
        <button onclick="openModal('modal-create')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Registrar Tutor
        </button>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/20 text-success rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('tutores.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, documento o email" class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
        </form>
    </div>

    @if($padres->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron tutores</p>
            <button onclick="openModal('modal-create')" class="mt-3 inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar primer padre/tutor
            </button>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Tutor</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estudiantes a cargo</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Contacto</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($padres as $padre)
                            @php
                                $colores = [
                                    'ACTIVO'   => 'bg-success/10 text-success',
                                    'INACTIVO' => 'bg-warning/10 text-warning',
                                ];
                                $tutorizados = $padre->students
                                    ->filter(fn($s) => $s->pivot->es_principal)
                                    ->values();
                            @endphp
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors align-top">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $padre->apellidos }} {{ $padre->nombres }}</div>
                                    <div class="text-xs font-mono text-muted-foreground">{{ $padre->tipo_documento }} {{ $padre->numero_documento }}{{ $padre->complemento ? '-' . $padre->complemento : '' }}</div>
                                    @if($padre->user)
                                        <span class="inline-flex mt-1 items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary/10 text-primary">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            Con acceso
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($tutorizados->isEmpty())
                                        <span class="text-muted-foreground">—</span>
                                    @else
                                        <div class="flex flex-col gap-1">
                                            @foreach($tutorizados as $est)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg border border-muted bg-muted/40 text-xs text-foreground">
                                                    {{ $est->nombres }} {{ $est->apellidos }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    <div>{{ $padre->email ?? '—' }}</div>
                                    @if($padre->telefono)
                                        <div class="text-xs">{{ $padre->telefono }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$padre->estado] ?? 'bg-muted text-muted-foreground' }}">{{ $padre->estado }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @include('parent_guardians._acciones', ['padre' => $padre])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $padres->links('pagina.custom') }}
            </div>
        </div>
    @endif
@endsection

@push('modals')
    {{-- MODAL CREAR --}}
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:42rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Registrar Padre/Tutor</h2>
                    <button onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('tutores.store') }}" style="padding:1.5rem;">
                    @csrf
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                                <input type="text" name="nombres" required maxlength="150" value="{{ old('nombres') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                                <input type="text" name="apellidos" required maxlength="150" value="{{ old('apellidos') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                                <select name="tipo_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="CI" {{ old('tipo_documento', 'CI') === 'CI' ? 'selected' : '' }}>Cédula de Identidad</option>
                                    <option value="PASAPORTE" {{ old('tipo_documento') === 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                    <option value="OTRO" {{ old('tipo_documento') === 'OTRO' ? 'selected' : '' }}>Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                                <input type="text" name="numero_documento" required maxlength="20" value="{{ old('numero_documento') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" maxlength="5" value="{{ old('complemento') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" maxlength="20" value="{{ old('telefono') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo <span id="lbl-email-create" class="text-error hidden">*</span></label>
                                <input type="email" name="email" id="email-create" maxlength="150" value="{{ old('email') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" placeholder="Obligatorio si es tutor">
                                <p class="text-[11px] text-muted-foreground mt-1">El email es obligatorio cuando la persona es <strong>tutor</strong>, para otorgarle permisos de vista.</p>
                                @error('email') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                                <input type="text" name="direccion" maxlength="255" value="{{ old('direccion') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;" id="seccion-estudiantes-create">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Estudiantes a cargo</h3>
                            <button type="button" onclick="agregarFilaEstudiantes('create')" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar estudiante
                            </button>
                        </div>
                        <p class="text-[11px] text-muted-foreground mb-3">Seleccione los estudiantes y el tipo de relación. El <strong>tutor</strong> es obligatorio por estudiante y es quien recibe permisos de vista (requiere email).</p>
                        <div id="filas-estudiantes-create"></div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div id="modal-edit" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:42rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Padre/Tutor</h2>
                    <button onclick="closeModal('modal-edit')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="form-edit" method="POST" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                                <input type="text" name="nombres" id="edit-nombres" required maxlength="150" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                                <input type="text" name="apellidos" id="edit-apellidos" required maxlength="150" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo</label>
                                <select name="tipo_documento" id="edit-tipo_documento" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="CI">Cédula de Identidad</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                                <input type="text" name="numero_documento" id="edit-numero_documento" required maxlength="20" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" id="edit-complemento" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" id="edit-telefono" maxlength="20" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo <span id="lbl-email-edit" class="text-error hidden">*</span></label>
                                <input type="email" name="email" id="email-edit" maxlength="150" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" placeholder="Obligatorio si es tutor">
                                <p class="text-[11px] text-muted-foreground mt-1">El email es obligatorio cuando la persona es <strong>tutor</strong>, para otorgarle permisos de vista.</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                                <input type="text" name="direccion" id="edit-direccion" maxlength="255" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Estudiantes a cargo</h3>
                            <button type="button" onclick="agregarFilaEstudiantes('edit')" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar estudiante
                            </button>
                        </div>
                        <p class="text-[11px] text-muted-foreground mb-3">Seleccione los estudiantes y el tipo de relación. El <strong>tutor</strong> es obligatorio por estudiante y es quien recibe permisos de vista (requiere email).</p>
                        <div id="filas-estudiantes-edit"></div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Estado</h3>
                        <select name="estado" id="edit-estado" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-edit')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        const ESTUDIANTES = @json($estudiantes);

        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function opcionesEstudiantes(selectedId, modo) {
            let opts = '<option value="">— Seleccionar estudiante —</option>';
            ESTUDIANTES.forEach(function (e) {
                const sel = (String(selectedId) === String(e.id)) ? ' selected' : '';
                opts += '<option value="' + e.id + '"' + sel + '>' + e.apellidos + ' ' + e.nombres + (e.codigo ? ' (' + e.codigo + ')' : '') + '</option>';
            });
            return opts;
        }

        function opcionesTipo(selected, modo) {
            const tipos = ['PADRE', 'MADRE', 'TUTOR'];
            let opts = '<option value=""' + (selected ? '' : ' selected') + '>— Tipo —</option>';
            tipos.forEach(function (t) {
                const sel = (selected === t) ? ' selected' : '';
                opts += '<option value="' + t + '"' + sel + '>' + t.charAt(0) + t.slice(1).toLowerCase() + '</option>';
            });
            return opts;
        }

        function nuevaFilaEstudiantes(modo, datos) {
            datos = datos || {};
            const contenedor = document.getElementById('filas-estudiantes-' + modo);
            const fila = document.createElement('div');
            fila.className = 'fila-estudiante flex flex-col sm:flex-row gap-2 mb-2';
            fila.innerHTML =
                '<div class="flex-1">' +
                    '<select name="students[' + (contenedor.children.length) + '][id]" onchange="actualizarCorreoRequerido()" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">' + opcionesEstudiantes(datos.id, modo) + '</select>' +
                '</div>' +
                '<div class="w-full sm:w-40">' +
                    '<select name="students[' + (contenedor.children.length) + '][tipo_relacion]" onchange="actualizarCorreoRequerido()" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">' + opcionesTipo(datos.tipo, modo) + '</select>' +
                '</div>' +
                '<button type="button" onclick="this.parentElement.remove(); actualizarCorreoRequerido();" class="px-3 py-2.5 rounded-lg border border-error/20 text-error hover:bg-error/10 transition-colors cursor-pointer" title="Quitar">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>';
            contenedor.appendChild(fila);
        }

        function agregarFilaEstudiantes(modo) {
            nuevaFilaEstudiantes(modo, {});
            actualizarCorreoRequerido();
        }

        function tieneTutorSeleccionado() {
            const filas = document.querySelectorAll('.fila-estudiante');
            for (const fila of filas) {
                const tipo = fila.querySelector('select[name$="[tipo_relacion]"]');
                if (tipo && tipo.value === 'TUTOR') return true;
            }
            return false;
        }

        function actualizarCorreoRequerido() {
            const hayTutor = tieneTutorSeleccionado();
            ['create', 'edit'].forEach(function (modo) {
                const input = document.getElementById('email-' + modo);
                const lbl = document.getElementById('lbl-email-' + modo);
                if (input) input.required = hayTutor;
                if (lbl) lbl.classList.toggle('hidden', !hayTutor);
            });
        }

        function editarPadre(datos) {
            document.getElementById('form-edit').action = '/tutores/' + datos.id;
            document.getElementById('edit-nombres').value = datos.nombres ?? '';
            document.getElementById('edit-apellidos').value = datos.apellidos ?? '';
            document.getElementById('edit-tipo_documento').value = datos.tipo_documento || 'CI';
            document.getElementById('edit-numero_documento').value = datos.numero_documento ?? '';
            document.getElementById('edit-complemento').value = datos.complemento ?? '';
            document.getElementById('edit-telefono').value = datos.telefono ?? '';
            document.getElementById('email-edit').value = datos.email ?? '';
            document.getElementById('edit-direccion').value = datos.direccion ?? '';
            document.getElementById('edit-estado').value = datos.estado || 'ACTIVO';
            document.getElementById('filas-estudiantes-edit').innerHTML = '';
            (datos.students || []).forEach(function (e) {
                nuevaFilaEstudiantes('edit', { id: e.id, tipo: e.tipo });
            });
            actualizarCorreoRequerido();
            openModal('modal-edit');
        }
    </script>
@endpush
