@extends('layouts.app')
@section('titulo', 'Estudiantes')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Estudiantes</h1>
            <p class="text-sm text-muted-foreground">Gestión de estudiantes del colegio</p>
        </div>
        <button onclick="openModal('modal-create')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Estudiante
        </button>
    </div>

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('students.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, código o documento..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                <option value="EGRESADO" {{ request('estado') === 'EGRESADO' ? 'selected' : '' }}>Egresado</option>
                <option value="TRASLADADO" {{ request('estado') === 'TRASLADADO' ? 'selected' : '' }}>Trasladado</option>
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Buscar</button>
        </form>
    </div>

    @if($students->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron estudiantes</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Nombre</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Documento</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Sexo</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ $student->codigo }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $student->tipo_documento }} {{ $student->numero_documento }}{{ $student->complemento ? '-' . $student->complemento : '' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $student->sexo === 'M' ? 'Masculino' : 'Femenino' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $colores = [
                                            'ACTIVO'    => 'bg-success/10 text-success',
                                            'INACTIVO'  => 'bg-warning/10 text-warning',
                                            'EGRESADO'  => 'bg-info/10 text-info',
                                            'TRASLADADO' => 'bg-neutral-100 text-neutral-600',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$student->estado] ?? 'bg-muted text-muted-foreground' }}">
                                        {{ $student->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button onclick="verEstudiante(this)" data-id="{{ $student->id }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <button onclick="editarEstudiante(this)" data-id="{{ $student->id }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('¿Estás seguro de eliminar este estudiante?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-error/10 transition-colors text-muted-foreground hover:text-error cursor-pointer" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $students->links('pagina.custom') }}
            </div>
        </div>

        @if($eliminadosCount > 0)
            <div class="flex justify-end mt-3">
                <a href="{{ route('students.eliminados') }}" class="text-sm text-muted-foreground hover:text-error transition-colors">
                    Ver eliminados ({{ $eliminadosCount }}) →
                </a>
            </div>
        @endif
    @endif
@endsection

@push('modals')
    {{-- MODAL CREAR --}}
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Registrar Nuevo Estudiante</h2>
                    <button onclick="closeModal('modal-create')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" style="padding:1.5rem;">
                    @csrf
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                                <input type="text" name="nombres" value="{{ old('nombres') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('nombres') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                                <input type="text" name="apellidos" value="{{ old('apellidos') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('apellidos') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de Nacimiento <span class="text-error">*</span></label>
                                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('fecha_nacimiento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar de Nacimiento</label>
                                <input type="text" name="lugar_nacimiento" value="{{ old('lugar_nacimiento') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('lugar_nacimiento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sexo <span class="text-error">*</span></label>
                                <select name="sexo" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar...</option>
                                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                                @error('sexo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                                <select name="estado" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                    <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="EGRESADO" {{ old('estado') === 'EGRESADO' ? 'selected' : '' }}>Egresado</option>
                                    <option value="TRASLADADO" {{ old('estado') === 'TRASLADADO' ? 'selected' : '' }}>Trasladado</option>
                                </select>
                                @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
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
                                @error('tipo_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                                <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('numero_documento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" value="{{ old('complemento') }}" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('complemento') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                                <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('direccion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @error('telefono') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo Electrónico del Estudiante</label>
                                <input type="email" name="correo_estudiante" value="{{ old('correo_estudiante') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Si se deja vacío, se generará: nombre.apellido@sanber.edu.bo</p>
                                @error('correo_estudiante') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Familia (Opcional)</h3>
                        <p class="text-xs text-muted-foreground mb-4">Agregue el padre y/o la madre si desea registrarlos. Al final seleccione quién será el <strong>tutor</strong>. Si no tiene padre ni madre, puede agregar directamente un tutor.</p>

                        <button type="button" id="btn-agregar-padre" onclick="toggleBloque('padre')" class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/20 transition-colors mb-3 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar padre
                        </button>
                        <div id="bloque-padre" style="display:none; margin-bottom:1.5rem;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                                <h4 class="text-sm font-semibold text-foreground">Datos del Padre</h4>
                                <button type="button" onclick="toggleBloque('padre')" class="text-xs text-error hover:underline cursor-pointer">Quitar padre</button>
                            </div>
                            <div style="margin-bottom:0.75rem;"><input type="text" id="buscador-padre" placeholder="Buscar padre por CI o nombre..." class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" oninput="buscarPersona('padre', this.value)"><div id="resultados-padre" class="hidden"></div></div>
                            <input type="hidden" name="padre_id_existente" id="padre_id_existente">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" name="padre_nombres" id="padre_nombres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" name="padre_apellidos" id="padre_apellidos" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo Doc.</label><select name="padre_tipo_doc" id="padre_tipo_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"><option value="CI">CI</option><option value="PASAPORTE">Pasaporte</option><option value="OTRO">Otro</option></select></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" name="padre_num_doc" id="padre_num_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label><input type="text" name="padre_complemento" id="padre_complemento" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" name="padre_telefono" id="padre_telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                            </div>
                        </div>

                        <button type="button" id="btn-agregar-madre" onclick="toggleBloque('madre')" class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/20 transition-colors mb-3 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar madre
                        </button>
                        <div id="bloque-madre" style="display:none; margin-bottom:1.5rem;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                                <h4 class="text-sm font-semibold text-foreground">Datos de la Madre</h4>
                                <button type="button" onclick="toggleBloque('madre')" class="text-xs text-error hover:underline cursor-pointer">Quitar madre</button>
                            </div>
                            <div style="margin-bottom:0.75rem;"><input type="text" id="buscador-madre" placeholder="Buscar madre por CI o nombre..." class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" oninput="buscarPersona('madre', this.value)"><div id="resultados-madre" class="hidden"></div></div>
                            <input type="hidden" name="madre_id_existente" id="madre_id_existente">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" name="madre_nombres" id="madre_nombres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" name="madre_apellidos" id="madre_apellidos" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo Doc.</label><select name="madre_tipo_doc" id="madre_tipo_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"><option value="CI">CI</option><option value="PASAPORTE">Pasaporte</option><option value="OTRO">Otro</option></select></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" name="madre_num_doc" id="madre_num_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label><input type="text" name="madre_complemento" id="madre_complemento" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" name="madre_telefono" id="madre_telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">¿Quién es el Tutor? <span class="text-error">*</span></h3>
                        <p class="text-xs text-muted-foreground mb-3">El tutor es quien recibirá acceso al sistema para el seguimiento del estudiante. Si no registró padre ni madre, elija "Otro tutor".</p>
                        <div class="flex flex-col sm:flex-row gap-3 mb-4">
                            <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" id="qt-padre" name="quien_es_tutor" value="PADRE" {{ old('quien_es_tutor', 'PADRE') === 'PADRE' ? 'checked' : '' }} class="accent-primary" onchange="toggleTutorCampos(this)"> Padre</label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" id="qt-madre" name="quien_es_tutor" value="MADRE" {{ old('quien_es_tutor') === 'MADRE' ? 'checked' : '' }} class="accent-primary" onchange="toggleTutorCampos(this)"> Madre</label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" id="qt-tutor" name="quien_es_tutor" value="TUTOR" {{ old('quien_es_tutor') === 'TUTOR' ? 'checked' : '' }} class="accent-primary" onchange="toggleTutorCampos(this)"> Otro tutor</label>
                        </div>
                        @error('quien_es_tutor') <p class="text-xs text-error mb-3">{{ $message }}</p> @enderror

                        <div id="campos-tutor-externo" style="display:{{ old('quien_es_tutor') === 'TUTOR' ? 'block' : 'none' }};">
                            <div style="margin-bottom:0.75rem;"><input type="text" id="buscador-tutor" placeholder="Buscar tutor por CI o nombre..." class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" oninput="buscarPersona('tutor', this.value)"><div id="resultados-tutor" class="hidden"></div></div>
                            <input type="hidden" name="tutor_id_existente" id="tutor_id_existente">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label><input type="text" name="tutor_nombres" id="tutor_nombres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label><input type="text" name="tutor_apellidos" id="tutor_apellidos" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo Doc. <span class="text-error">*</span></label><select name="tutor_tipo_doc" id="tutor_tipo_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"><option value="CI">CI</option><option value="PASAPORTE">Pasaporte</option><option value="OTRO">Otro</option></select></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento <span class="text-error">*</span></label><input type="text" name="tutor_num_doc" id="tutor_num_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label><input type="text" name="tutor_complemento" id="tutor_complemento" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" name="tutor_telefono" id="tutor_telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo <span class="text-error">*</span></label><input type="email" name="tutor_correo" id="tutor_correo" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"><p class="text-xs text-muted-foreground mt-1">Se usará para acceder al sistema.</p></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label><input type="text" name="tutor_direccion" id="tutor_direccion" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Ocupación</label><input type="text" name="tutor_ocupacion" id="tutor_ocupacion" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                            </div>
                        </div>
                        <div id="correo-tutor-padre-madre" style="display:{{ old('quien_es_tutor', 'PADRE') !== 'TUTOR' ? 'block' : 'none' }}; margin-top:1rem;">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del Tutor (para acceso al sistema) <span class="text-error">*</span></label>
                            <input type="email" name="tutor_correo" id="tutor_correo_common" value="{{ old('tutor_correo') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <p class="text-xs text-muted-foreground mt-1">Correo personal del padre/madre seleccionado como tutor.</p>
                            @error('tutor_correo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar Estudiante</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL VER DETALLE --}}
    <div id="modal-view" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Detalle del Estudiante</h2>
                    <button onclick="closeModal('modal-view')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div style="padding:1.5rem;">
                    <div style="display:flex; gap:1.5rem; margin-bottom:1.25rem;">
                        <div style="flex-shrink:0; width:6rem; height:7.5rem; border-radius:0.75rem; overflow:hidden; background:var(--muted); display:flex; align-items:center; justify-content:center;">
                            <img id="v-fotografia" src="" alt="" style="width:100%; height:100%; object-fit:cover; display:none;">
                            <svg id="v-fotografia-placeholder" class="w-10 h-10" style="color:var(--muted-foreground); opacity:0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div style="flex:1;">
                            <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                                <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Código</span><span class="font-mono font-medium text-foreground" id="v-codigo"></span></div>
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Estado</span><span class="text-foreground" id="v-estado"></span></div>
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Nombres</span><span class="text-foreground" id="v-nombres"></span></div>
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Apellidos</span><span class="text-foreground" id="v-apellidos"></span></div>
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Fecha de Nacimiento</span><span class="text-foreground" id="v-fecha_nacimiento"></span></div>
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Lugar de Nacimiento</span><span class="text-foreground" id="v-lugar_nacimiento"></span></div>
                                    <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Sexo</span><span class="text-foreground" id="v-sexo"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Tipo</span><span class="text-foreground" id="v-tipo_documento"></span></div>
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Número</span><span class="text-foreground font-medium" id="v-numero_documento"></span></div>
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Complemento</span><span class="text-foreground" id="v-complemento"></span></div>
                        </div>
                    </div>
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Dirección</span><span class="text-foreground" id="v-direccion"></span></div>
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Teléfono</span><span class="text-foreground" id="v-telefono"></span></div>
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Correo</span><span class="text-foreground" id="v-correo"></span></div>
                        </div>
                    </div>
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Familia (Tutor · Padre · Madre)</h3>
                        <div id="v-familia-content" class="text-sm"></div>
                    </div>
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Información Adicional</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Idioma</span><span class="text-foreground" id="v-idioma"></span></div>
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Cultura</span><span class="text-foreground" id="v-cultura"></span></div>
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Discapacidad</span><span class="text-foreground" id="v-discapacidad"></span></div>
                        </div>
                    </div>
                    <br>
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05);">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Historial de Cambios</h3>
                        <div id="v-historial-content" class="text-sm text-muted-foreground">Sin cambios registrados.</div>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-view')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div id="modal-edit" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Estudiante</h2>
                    <button onclick="closeModal('modal-edit')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="form-edit" method="POST" enctype="multipart/form-data" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                                <input type="text" name="nombres" id="e-nombres" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                                <input type="text" name="apellidos" id="e-apellidos" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de Nacimiento <span class="text-error">*</span></label>
                                <input type="date" name="fecha_nacimiento" id="e-fecha_nacimiento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar de Nacimiento</label>
                                <input type="text" name="lugar_nacimiento" id="e-lugar_nacimiento" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sexo <span class="text-error">*</span></label>
                                <select name="sexo" id="e-sexo" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                                <select name="estado" id="e-estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                    <option value="EGRESADO">Egresado</option>
                                    <option value="TRASLADADO">Trasladado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                                <select name="tipo_documento" id="e-tipo_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="CI">Cédula de Identidad</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                                <input type="text" name="numero_documento" id="e-numero_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" id="e-complemento" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                                <input type="text" name="direccion" id="e-direccion" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" id="e-telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del Estudiante</label>
                                <input type="email" name="correo_estudiante" id="e-correo_estudiante" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener el actual.</p>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Familia (Opcional)</h3>
                        <p class="text-xs text-muted-foreground mb-4">Agregue el padre y/o la madre si desea registrarlos. Al final seleccione quién será el <strong>tutor</strong>. Si no tiene padre ni madre, puede agregar directamente un tutor.</p>

                        <button type="button" id="btn-agregar-padre-edit" onclick="toggleBloqueEdit('padre')" class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/20 transition-colors mb-3 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar padre
                        </button>
                        <div id="bloque-padre-edit" style="display:none; margin-bottom:1.5rem;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                                <h4 class="text-sm font-semibold text-foreground">Datos del Padre</h4>
                                <button type="button" onclick="toggleBloqueEdit('padre')" class="text-xs text-error hover:underline cursor-pointer">Quitar padre</button>
                            </div>
                            <input type="hidden" name="padre_id_existente" id="e-padre_id_existente">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" name="padre_nombres" id="e-padre_nombres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" name="padre_apellidos" id="e-padre_apellidos" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" name="padre_num_doc" id="e-padre_num_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" name="padre_telefono" id="e-padre_telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                            </div>
                        </div>

                        <button type="button" id="btn-agregar-madre-edit" onclick="toggleBloqueEdit('madre')" class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/20 transition-colors mb-3 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar madre
                        </button>
                        <div id="bloque-madre-edit" style="display:none; margin-bottom:1.5rem;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                                <h4 class="text-sm font-semibold text-foreground">Datos de la Madre</h4>
                                <button type="button" onclick="toggleBloqueEdit('madre')" class="text-xs text-error hover:underline cursor-pointer">Quitar madre</button>
                            </div>
                            <input type="hidden" name="madre_id_existente" id="e-madre_id_existente">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" name="madre_nombres" id="e-madre_nombres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" name="madre_apellidos" id="e-madre_apellidos" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" name="madre_num_doc" id="e-madre_num_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" name="madre_telefono" id="e-madre_telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">¿Quién es el Tutor?</h3>
                        <div class="flex flex-col sm:flex-row gap-3 mb-4">
                            <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="quien_es_tutor" value="PADRE" id="e-qt-padre" class="accent-primary"> Padre</label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="quien_es_tutor" value="MADRE" id="e-qt-madre" class="accent-primary"> Madre</label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="quien_es_tutor" value="TUTOR" id="e-qt-tutor" class="accent-primary"> Otro tutor</label>
                        </div>
                        <div id="e-campos-tutor-externo" style="display:none;">
                            <input type="hidden" name="tutor_id_existente" id="e-tutor_id_existente">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" name="tutor_nombres" id="e-tutor_nombres" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" name="tutor_apellidos" id="e-tutor_apellidos" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" name="tutor_num_doc" id="e-tutor_num_doc" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" name="tutor_telefono" id="e-tutor_telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo</label><input type="email" name="tutor_correo" id="e-tutor_correo" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></div>
                            </div>
                        </div>
                        <div id="e-correo-tutor-padre-madre">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del Tutor (para acceso al sistema)</label>
                            <input type="email" name="tutor_correo" id="e-tutor_correo_common" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <p class="text-xs text-muted-foreground mt-1">Correo del padre/madre seleccionado como tutor.</p>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Información Adicional</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Idioma</label>
                                <input type="text" name="idioma" id="e-idioma" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Cultura</label>
                                <input type="text" name="cultura" id="e-cultura" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Discapacidad</label>
                                <input type="text" name="discapacidad" id="e-discapacidad" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fotografía</label>
                                <input type="file" name="fotografia" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener la actual.</p>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-edit')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(old('nombres') || $errors->any())
        <script>openModal('modal-create')</script>
    @endif

    <script>
        var estudiantesMap = {
            @foreach($students as $student)
            {{ $student->id }}: @json($student->toArray()){{ $loop->last ? '' : ',' }}
            @endforeach
        };

        function formatFecha(fecha) {
            if (!fecha) return '—';
            var d = new Date(fecha);
            var dia = String(d.getDate()).padStart(2, '0');
            var mes = String(d.getMonth() + 1).padStart(2, '0');
            var anio = d.getFullYear();
            var hoy = new Date();
            var edad = hoy.getFullYear() - d.getFullYear();
            if (hoy.getMonth() < d.getMonth() || (hoy.getMonth() === d.getMonth() && hoy.getDate() < d.getDate())) edad--;
            return dia + '/' + mes + '/' + anio + ' (' + edad + ' años)';
        }

        function verEstudiante(btn) {
            var s = estudiantesMap[btn.dataset.id];
            if (!s) return;
            var img = document.getElementById('v-fotografia');
            var ph = document.getElementById('v-fotografia-placeholder');
            if (s.fotografia) {
                img.src = '{{ URL::to("/") }}/storage/' + s.fotografia;
                img.style.display = 'block';
                ph.style.display = 'none';
            } else {
                img.style.display = 'none';
                ph.style.display = 'block';
            }
            document.getElementById('v-codigo').textContent = s.codigo || '—';
            document.getElementById('v-estado').textContent = s.estado || '—';
            document.getElementById('v-nombres').textContent = s.nombres || '—';
            document.getElementById('v-apellidos').textContent = s.apellidos || '—';
            document.getElementById('v-fecha_nacimiento').textContent = formatFecha(s.fecha_nacimiento);
            document.getElementById('v-lugar_nacimiento').textContent = s.lugar_nacimiento || '—';
            document.getElementById('v-sexo').textContent = s.sexo === 'M' ? 'Masculino' : s.sexo === 'F' ? 'Femenino' : '—';
            document.getElementById('v-tipo_documento').textContent = s.tipo_documento || '—';
            document.getElementById('v-numero_documento').textContent = s.numero_documento || '—';
            document.getElementById('v-complemento').textContent = s.complemento || '—';
            document.getElementById('v-direccion').textContent = s.direccion || '—';
            document.getElementById('v-telefono').textContent = s.telefono || '—';
            document.getElementById('v-correo').textContent = s.correo || '—';
            document.getElementById('v-idioma').textContent = s.idioma || '—';
            document.getElementById('v-cultura').textContent = s.cultura || '—';
            document.getElementById('v-discapacidad').textContent = s.discapacidad || '—';
            var pgs = s.parent_guardians || [];
            var padre = pgs.find(function(p) { return p.pivot && p.pivot.tipo_relacion === 'PADRE'; });
            var madre = pgs.find(function(p) { return p.pivot && p.pivot.tipo_relacion === 'MADRE'; });
            var tutorPg = pgs.find(function(p) { return p.pivot && p.pivot.tipo_relacion === 'TUTOR'; });
            var qt = s.quien_es_tutor || 'PADRE';
            var tutorRef = qt === 'PADRE' ? padre : qt === 'MADRE' ? madre : tutorPg;

            function filaFamiliar(rol, pg) {
                if (!pg) return '';
                var correo = (rol === 'TUTOR' && pg.correo) ? ' · ' + pg.correo : '';
                var fondo = (rol === 'TUTOR') ? '; background-color:rgba(124,58,237,0.06)' : '';
                return '<div style="border:1px solid ' + (rol === 'TUTOR' ? 'rgba(124,58,237,0.35)' : 'var(--border)') + '; border-radius:0.5rem; padding:0.5rem 0.75rem; margin-bottom:0.5rem' + fondo + '">' +
                    '<div class="text-xs font-semibold uppercase tracking-wider ' + (rol === 'TUTOR' ? 'text-primary' : 'text-muted-foreground') + '">' + (rol === 'TUTOR' ? 'Tutor principal' : rol) + '</div>' +
                    '<div class="font-medium text-foreground">' + pg.nombres + ' ' + pg.apellidos + '</div>' +
                    '<div class="text-xs text-muted-foreground">' + (pg.numero_documento ? 'CI ' + pg.numero_documento : '') + correo + '</div>' +
                    '</div>';
            }

            var famHtml = filaFamiliar('TUTOR', tutorRef);
            famHtml += filaFamiliar('PADRE', padre);
            famHtml += filaFamiliar('MADRE', madre);
            if (!famHtml) famHtml = '<span class="text-muted-foreground">Sin datos familiares registrados</span>';
            document.getElementById('v-familia-content').innerHTML = famHtml;
            var ops = s.operations || [];
            var histHtml = '';
            if (ops.length === 0) {
                histHtml = 'Sin cambios registrados.';
            } else {
                histHtml = '<table style="width:100%; border-collapse:collapse; font-size:0.8125rem;">';
                histHtml += '<thead><tr style="text-align:left; border-bottom:1px solid var(--border);"><th style="padding:0.375rem 0.5rem; font-weight:600; color:var(--foreground);">Fecha</th><th style="padding:0.375rem 0.5rem; font-weight:600; color:var(--foreground);">Usuario</th><th style="padding:0.375rem 0.5rem; font-weight:600; color:var(--foreground);">Acción</th><th style="padding:0.375rem 0.5rem; font-weight:600; color:var(--foreground);">Detalle</th></tr></thead><tbody>';
                for (var i = 0; i < ops.length; i++) {
                    var op = ops[i];
                    var u = op.user ? (op.user.nombre + ' ' + op.user.apellido) : 'Sistema';
                    histHtml += '<tr style="border-bottom:1px solid var(--border);"><td style="padding:0.375rem 0.5rem; white-space:nowrap; color:var(--muted-foreground);">' + (op.created_at || '') + '</td><td style="padding:0.375rem 0.5rem; color:var(--foreground);">' + u + '</td><td style="padding:0.375rem 0.5rem;"><span style="display:inline-block; padding:0.125rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:500; background:var(--muted); color:var(--muted-foreground);">' + (op.accion || '—') + '</span></td><td style="padding:0.375rem 0.5rem; color:var(--muted-foreground);">' + (op.detalle || '') + '</td></tr>';
                }
                histHtml += '</tbody></table>';
            }
            document.getElementById('v-historial-content').innerHTML = histHtml;
            openModal('modal-view');
        }

        function editarEstudiante(btn) {
            var s = estudiantesMap[btn.dataset.id];
            if (!s) return;
            document.getElementById('form-edit').action = '{{ url("students") }}/' + s.id;
            document.getElementById('e-nombres').value = s.nombres || '';
            document.getElementById('e-apellidos').value = s.apellidos || '';
            document.getElementById('e-fecha_nacimiento').value = s.fecha_nacimiento ? s.fecha_nacimiento.substring(0, 10) : '';
            document.getElementById('e-lugar_nacimiento').value = s.lugar_nacimiento || '';
            document.getElementById('e-sexo').value = s.sexo || '';
            document.getElementById('e-estado').value = s.estado || 'ACTIVO';
            document.getElementById('e-tipo_documento').value = s.tipo_documento || 'CI';
            document.getElementById('e-numero_documento').value = s.numero_documento || '';
            document.getElementById('e-complemento').value = s.complemento || '';
            document.getElementById('e-direccion').value = s.direccion || '';
            document.getElementById('e-telefono').value = s.telefono || '';
            document.getElementById('e-correo_estudiante').value = s.correo || '';
            document.getElementById('e-idioma').value = s.idioma || '';
            document.getElementById('e-cultura').value = s.cultura || '';
            document.getElementById('e-discapacidad').value = s.discapacidad || '';
            var pgs = s.parent_guardians || [];
            var padre = pgs.find(function(p) { return p.pivot && p.pivot.tipo_relacion === 'PADRE'; });
            var madre = pgs.find(function(p) { return p.pivot && p.pivot.tipo_relacion === 'MADRE'; });
            var tutorPg = pgs.find(function(p) { return p.pivot && p.pivot.tipo_relacion === 'TUTOR'; });

            function fillPersona(prefix, pg) {
                if (!pg) return;
                document.getElementById(prefix + '_id_existente').value = pg.id || '';
                document.getElementById(prefix + '_nombres').value = pg.nombres || '';
                document.getElementById(prefix + '_apellidos').value = pg.apellidos || '';
                document.getElementById(prefix + '_num_doc').value = pg.numero_documento || '';
                document.getElementById(prefix + '_telefono').value = pg.telefono || '';
                if (prefix === 'tutor') {
                    document.getElementById('e-tutor_correo').value = pg.correo || '';
                }
            }
            if (padre) {
                var bpE = document.getElementById('bloque-padre-edit');
                if (bpE) bpE.style.display = 'block';
                var bpBtnE = document.getElementById('btn-agregar-padre-edit');
                if (bpBtnE) bpBtnE.style.display = 'none';
            }
            if (madre) {
                var bmE = document.getElementById('bloque-madre-edit');
                if (bmE) bmE.style.display = 'block';
                var bmBtnE = document.getElementById('btn-agregar-madre-edit');
                if (bmBtnE) bmBtnE.style.display = 'none';
            }
            fillPersona('padre', padre);
            fillPersona('madre', madre);

            var qt = s.quien_es_tutor || 'PADRE';
            document.getElementById('e-qt-' + qt.toLowerCase()).checked = true;
            document.getElementById('e-campos-tutor-externo').style.display = qt === 'TUTOR' ? 'block' : 'none';
            document.getElementById('e-correo-tutor-padre-madre').style.display = qt !== 'TUTOR' ? 'block' : 'none';
            actualizarRadiosTutorEdit();
            if (qt === 'TUTOR') fillPersona('tutor', tutorPg);
            else {
                var tCorreo = qt === 'PADRE' ? (padre ? padre.correo : '') : (madre ? madre.correo : '');
                document.getElementById('e-tutor_correo_common').value = tCorreo;
            }
            openModal('modal-edit');
        }

        function toggleTutorCampos(radio) {
            var modal = radio.closest('[id^="modal-"]');
            var prefix = modal && modal.id === 'modal-edit' ? 'e-' : '';
            var ext = document.getElementById(prefix + 'campos-tutor-externo');
            var cmm = document.getElementById(prefix + 'correo-tutor-padre-madre');
            if (!ext || !cmm) return;
            if (radio.value === 'TUTOR') { ext.style.display = 'block'; cmm.style.display = 'none'; }
            else { ext.style.display = 'none'; cmm.style.display = 'block'; }
        }
        document.querySelectorAll('input[name="quien_es_tutor"]').forEach(function(r) {
            r.addEventListener('change', function() { toggleTutorCampos(this); });
        });

        // Buscar persona existente
        var busquedaTimeout = null;
        function buscarPersona(tipo, query) {
            var dd = document.getElementById('resultados-' + tipo);
            clearTimeout(busquedaTimeout);
            if (query.length < 2) { dd.classList.add('hidden'); dd.innerHTML = ''; return; }
            busquedaTimeout = setTimeout(function() {
                fetch('{{ route("students.buscarPersona") }}?q=' + encodeURIComponent(query))
                .then(r => r.json()).then(data => {
                    if (!data.length) { dd.classList.add('hidden'); return; }
                    dd.innerHTML = data.map(p =>
                        '<div class="px-3 py-2 cursor-pointer hover:bg-muted text-sm border-b border-border last:border-0" onclick="seleccionarPersona(\'' + tipo + '\', ' + p.id + ', \'' + (p.nombres||'').replace(/'/g,"\\'") + '\', \'' + (p.apellidos||'').replace(/'/g,"\\'") + '\', \'' + (p.tipo_documento||'CI') + '\', \'' + (p.numero_documento||'') + '\', \'' + (p.complemento||'') + '\', \'' + (p.telefono||'') + '\', \'' + (p.correo||'') + '\')">' +
                        '<span class="font-semibold">' + (p.nombres||'') + ' ' + (p.apellidos||'') + '</span> — ' + (p.numero_documento||'') +
                        '</div>'
                    ).join('');
                    dd.classList.remove('hidden');
                });
            }, 300);
        }

        function seleccionarPersona(tipo, id, nombres, apellidos, tipoDoc, numDoc, comp, tel, correo) {
            document.getElementById(tipo + '_id_existente').value = id;
            document.getElementById(tipo + '_nombres').value = nombres;
            document.getElementById(tipo + '_apellidos').value = apellidos;
            document.getElementById(tipo + '_num_doc').value = numDoc;
            document.getElementById(tipo + '_complemento').value = comp;
            document.getElementById(tipo + '_telefono').value = tel;
            if (tipo === 'tutor') {
                document.getElementById('tutor_correo').value = correo;
            }
            document.getElementById('resultados-' + tipo).classList.add('hidden');
            document.getElementById('buscador-' + tipo).value = '';
        }

        document.addEventListener('click', function(e) {
            ['padre','madre','tutor'].forEach(function(t) {
                var dd = document.getElementById('resultados-' + t);
                if (dd && !dd.contains(e.target) && e.target.id !== 'buscador-' + t) dd.classList.add('hidden');
            });
        });

        function toggleBloque(tipo) {
            var bloque = document.getElementById('bloque-' + tipo);
            var btn = document.getElementById('btn-agregar-' + tipo);
            if (!bloque) return;
            var visible = bloque.style.display !== 'none';
            if (visible) {
                bloque.style.display = 'none';
                if (btn) { btn.style.display = ''; }
            } else {
                bloque.style.display = 'block';
                if (btn) { btn.style.display = 'none'; }
            }
            actualizarRadiosTutor();
        }

        function toggleBloqueEdit(tipo) {
            var bloque = document.getElementById('bloque-' + tipo + '-edit');
            var btn = document.getElementById('btn-agregar-' + tipo + '-edit');
            if (!bloque) return;
            if (bloque.style.display !== 'none') {
                bloque.style.display = 'none';
                if (btn) btn.style.display = '';
            } else {
                bloque.style.display = 'block';
                if (btn) btn.style.display = 'none';
            }
            actualizarRadiosTutorEdit();
        }

        function actualizarRadiosTutorEdit() {
            var bloquePadre = document.getElementById('bloque-padre-edit');
            var bloqueMadre = document.getElementById('bloque-madre-edit');
            var padreVisible = !!bloquePadre && bloquePadre.style.display !== 'none';
            var madreVisible = !!bloqueMadre && bloqueMadre.style.display !== 'none';
            var qPadre = document.getElementById('e-qt-padre');
            var qMadre = document.getElementById('e-qt-madre');
            var qTutor = document.getElementById('e-qt-tutor');
            if (qPadre) qPadre.disabled = !padreVisible;
            if (qMadre) qMadre.disabled = !madreVisible;
            if (qPadre && qPadre.checked && !padreVisible && qTutor) qTutor.checked = true;
            if (qMadre && qMadre.checked && !madreVisible && qTutor) qTutor.checked = true;
        }

        function actualizarRadiosTutor() {
            var bloquePadre = document.getElementById('bloque-padre');
            var bloqueMadre = document.getElementById('bloque-madre');
            var padreVisible = !!bloquePadre && bloquePadre.style.display !== 'none';
            var madreVisible = !!bloqueMadre && bloqueMadre.style.display !== 'none';
            var qPadre = document.getElementById('qt-padre');
            var qMadre = document.getElementById('qt-madre');
            var qTutor = document.getElementById('qt-tutor');
            if (qPadre) qPadre.disabled = !padreVisible;
            if (qMadre) qMadre.disabled = !madreVisible;
            if (qPadre && qPadre.checked && !padreVisible && qTutor) qTutor.checked = true;
            if (qMadre && qMadre.checked && !madreVisible && qTutor) qTutor.checked = true;
            if (qTutor && qTutor.checked) {
                var ext = document.getElementById('campos-tutor-externo');
                var cm = document.getElementById('correo-tutor-padre-madre');
                if (ext) ext.style.display = 'block';
                if (cm) cm.style.display = 'none';
            }
        }

        if (document.getElementById('bloque-padre')) actualizarRadiosTutor();
    </script>
@endpush
