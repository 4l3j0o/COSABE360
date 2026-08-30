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
        <div class="flex items-center gap-2">
            <a href="{{ route('student_documents.pending') }}" class="inline-flex items-center gap-2 bg-card border border-border text-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-muted transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </a>
            <button onclick="openModal('modal-create')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('students.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, código o documento..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="estado_academico" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados académicos</option>
                <option value="MATRICULADO" {{ request('estado_academico') === 'MATRICULADO' ? 'selected' : '' }}>Matriculados</option>
                <option value="INSCRITO" {{ request('estado_academico') === 'INSCRITO' ? 'selected' : '' }}>Inscritos</option>
                <option value="SIN_INSCRIBIR" {{ request('estado_academico') === 'SIN_INSCRIBIR' ? 'selected' : '' }}>Sin inscribir</option>
            </select>
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
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado académico</th>
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
                                    @php
                                        $acad = $estadosAcademicos[$student->id] ?? 'SIN_INSCRIBIR';
                                        $chip = [
                                            'MATRICULADO'  => ['bg-success/10 text-success', 'Matriculado', route('students.show', $student)],
                                            'INSCRITO'     => ['bg-info/10 text-info', 'Inscrito', route('estudiantes.matriculacion', $student)],
                                            'SIN_INSCRIBIR' => ['bg-muted text-muted-foreground', 'Sin inscribir', route('enrollments.index')],
                                        ][$acad];
                                    @endphp
                                    <a href="{{ $chip[2] }}" title="{{ $acad === 'SIN_INSCRIBIR' ? 'Ir a inscripciones' : 'Ver / matricular' }}" class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $chip[0] }} hover:opacity-80 transition-opacity">
                                        {{ $chip[1] }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('students.show', $student) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if(($estadosAcademicos[$student->id] ?? 'SIN_INSCRIBIR') === 'MATRICULADO')
                                            <a href="{{ route('monthly-payments.estudiante', $student) }}" class="p-1.5 rounded-lg hover:bg-primary/10 transition-colors text-muted-foreground hover:text-primary cursor-pointer" title="Ver pagos">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </a>
                                        @endif
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
                                <input type="email" name="email_estudiante" value="{{ old('email_estudiante') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Si se deja vacío, se generará: nombre.apellido@sanber.edu.bo</p>
                                @error('email_estudiante') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.25rem;">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Familia y Tutor <span class="text-error">*</span></h3>
                            <button type="button" id="btn-agregar-familiar" onclick="agregarFilaFamiliar()" class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-2 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar familiar
                            </button>
                        </div>
                        <div id="lista-familiares" style="display:none;"></div>
                        @error('familiares') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
                        @error('tutor_email') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
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
                            <div><span class="text-muted-foreground block text-xs uppercase tracking-wider">Correo</span><span class="text-foreground" id="v-email"></span></div>
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
                                <input type="email" name="email_estudiante" id="e-email_estudiante" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener el actual.</p>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.25rem;">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Familia y Tutor <span class="text-error">*</span></h3>
                            <button type="button" id="btn-agregar-familiar-edit" onclick="agregarFilaFamiliarEdit()" class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-2 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar familiar
                            </button>
                        </div>
                        <p class="text-xs text-muted-foreground mb-4">Agregue a los familiares del estudiante. Para cada uno elija su <strong>relación</strong> y marque quién será el <strong>tutor</strong> (debe haber exactamente uno). Si no es de la familia, use la relación <strong>TUTOR</strong>.</p>

                        <div id="lista-familiares-edit" style="display:none;"></div>
                        <div id="sin-familiar-edit" class="text-xs text-muted-foreground bg-muted/40 border border-dashed border-border rounded-lg px-4 py-5 text-center">
                            No hay familiares registrados para este estudiante. Use <strong>"Agregar familiar"</strong>.
                        </div>
                        @error('familiares') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
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

        var ETIQUETAS_RELACION = {
            PADRE: 'Padre',
            MADRE: 'Madre',
            TIO: 'Tío',
            TIA: 'Tía',
            ABUELO: 'Abuelo',
            ABUELA: 'Abuela',
            HERMANO: 'Hermano',
            HERMANA: 'Hermana',
            OTRO: 'Otro Familiar',
            TUTOR: 'Tutor (no familia)'
        };

        function construirFilaFamiliar(idx, pg, esEdit) {
            var p = pg || {};
            var relacion = (pg && pg.pivot) ? pg.pivot.tipo_relacion : 'PADRE';
            var esTutor = (pg && pg.pivot) ? (pg.pivot.es_principal ? 1 : 0) : 0;
            var sel = esEdit ? 'lista-familiares-edit' : 'lista-familiares';
            var opciones = '';
            var rels = ['PADRE','MADRE','TIO','TIA','ABUELO','ABUELA','HERMANO','HERMANA','OTRO','TUTOR'];
            for (var r = 0; r < rels.length; r++) {
                opciones += '<option value="' + rels[r] + '"' + (relacion === rels[r] ? ' selected' : '') + '>' + ETIQUETAS_RELACION[rels[r]] + '</option>';
            }
            return '<div class="fila-familiar" data-idx="' + idx + '" style="border:1px solid var(--border); border-radius:0.75rem; padding:1rem; margin-bottom:1rem; background:var(--card);">' +
                  '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">' +
                      '<h4 class="text-sm font-semibold text-foreground">Familiar</h4>' +
                      '<button type="button" onclick="quitarFilaFamiliar(this)" class="text-xs text-error hover:underline cursor-pointer">Quitar</button>' +
                  '</div>' +
                  '<div style="margin-bottom:0.75rem;">' +
                      '<input type="text" class="buscador-familiar" placeholder="Buscar persona existente por CI o nombre..." oninput="buscarPersonaFamiliar(this)" value="" style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' +
                      '<div class="resultados-familiar hidden"></div>' +
                  '</div>' +
                  '<input type="hidden" data-field="id_existente" name="familiares[' + idx + '][id_existente]" class="f-id" value="' + (p.id || '') + '">' +
                  '<input type="hidden" data-field="tipo_doc" name="familiares[' + idx + '][tipo_doc]" class="f-tipo_doc" value="' + (p.tipo_documento || 'CI') + '">' +
                  '<input type="hidden" data-field="complemento" name="familiares[' + idx + '][complemento]" class="f-complemento" value="' + (p.complemento || '') + '">' +
                  '<input type="hidden" data-field="direccion" name="familiares[' + idx + '][direccion]" class="f-direccion" value="' + (p.direccion || '') + '">' +
                  '<input type="hidden" data-field="ocupacion" name="familiares[' + idx + '][ocupacion]" class="f-ocupacion" value="' + (p.ocupacion || '') + '">' +
                  '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" data-field="nombres" name="familiares[' + idx + '][nombres]" class="f-nombres" value="' + (p.nombres || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" data-field="apellidos" name="familiares[' + idx + '][apellidos]" class="f-apellidos" value="' + (p.apellidos || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" data-field="num_doc" name="familiares[' + idx + '][num_doc]" class="f-num_doc" value="' + (p.numero_documento || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" data-field="telefono" name="familiares[' + idx + '][telefono]" class="f-telefono" value="' + (p.telefono || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Relación</label><select data-field="relacion" name="familiares[' + idx + '][relacion]" class="f-relacion" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' + opciones + '</select></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">¿Es tutor?</label><label class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" data-field="es_tutor" name="familiares[' + idx + '][es_tutor]" class="f-es_tutor" value="1" ' + (esTutor ? 'checked' : '') + ' onchange="marcarTutor(this)"> Es el tutor</label></div>' +
                  '</div>' +
                  '<div class="campo-email-tutor" style="' + (esTutor ? '' : 'display:none;') + 'margin-top:0.75rem;">' +
                      '<label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del tutor (para acceso al sistema) <span class="text-error">*</span></label>' +
                      '<input type="email" data-field="email" name="familiares[' + idx + '][email]" class="f-email" value="' + (p.email || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' +
                      '<p class="text-xs text-muted-foreground mt-1">El tutor es quien recibe el acceso al sistema.</p>' +
                  '</div>' +
              '</div>';
        }

        var familiarSeq = 0;
        function agregarFilaFamiliar() {
            var cont = document.getElementById('lista-familiares');
            cont.insertAdjacentHTML('beforeend', construirFilaFamiliar(familiarSeq, null, false));
            familiarSeq++;
            cont.style.display = 'block';
            document.getElementById('sin-familiar').style.display = 'none';
            renumerarFilas('lista-familiares');
        }

        var familiarSeqEdit = 0;
        function agregarFilaFamiliarEdit(editIdx) {
            var cont = document.getElementById('lista-familiares-edit');
            cont.insertAdjacentHTML('beforeend', construirFilaFamiliar(editIdx, null, true));
            familiarSeqEdit++;
            cont.style.display = 'block';
            var sn = document.getElementById('sin-familiar-edit');
            if (sn) sn.style.display = 'none';
            renumerarFilas('lista-familiares-edit');
        }

        function quitarFilaFamiliar(btn) {
            var fila = btn.closest('.fila-familiar');
            var cont = fila.parentNode;
            fila.remove();
            renumerarFilas(cont.id);
        }

        function renumerarFilas(contenedorId) {
            var cont = document.getElementById(contenedorId);
            if (!cont) return;
            var filas = cont.querySelectorAll('.fila-familiar');
            var idx = 0;
            filas.forEach(function(fila) {
                fila.dataset.idx = idx;
                fila.querySelectorAll('[data-field]').forEach(function(input) {
                    var campo = input.dataset.field;
                    input.name = 'familiares[' + idx + '][' + campo + ']';
                });
                var buscador = fila.querySelector('.buscador-familiar');
                if (buscador) buscador.oninput = (function(f){ return function(){ buscarPersonaFamiliar(f); }; })(buscador);
                idx++;
            });
            var vacio = (contenedorId === 'lista-familiares') ? 'sin-familiar' : 'sin-familiar-edit';
            if (idx === 0) {
                cont.style.display = 'none';
                var v = document.getElementById(vacio);
                if (v) v.style.display = 'block';
            }
        }

        var busquedaTimeout = null;
        function buscarPersonaFamiliar(input) {
            var fila = input.closest('.fila-familiar');
            var dd = fila.querySelector('.resultados-familiar');
            var query = input.value;
            clearTimeout(busquedaTimeout);
            if (query.length < 2) { dd.classList.add('hidden'); dd.innerHTML = ''; return; }
            busquedaTimeout = setTimeout(function() {
                fetch('{{ route("students.buscarPersona") }}?q=' + encodeURIComponent(query))
                .then(r => r.json()).then(data => {
                    if (!data.length) { dd.classList.add('hidden'); return; }
                    dd.innerHTML = data.map(function(p) {
                        return '<div class="px-3 py-2 cursor-pointer hover:bg-muted text-sm border-b border-border last:border-0" onclick="seleccionarPersonaFamiliar(this)">' +
                            '<span class="font-semibold">' + (p.nombres||'') + ' ' + (p.apellidos||'') + '</span> — ' + (p.numero_documento||'') +
                            '</div>';
                    }).join('');
                    dd.classList.remove('hidden');
                    dd._data = data;
                });
            }, 300);
        }

        function seleccionarPersonaFamiliar(el) {
            var fila = el.closest('.fila-familiar');
            var dd = fila.querySelector('.resultados-familiar');
            var data = dd._data || [];
            var li = Array.prototype.indexOf.call(dd.children, el);
            var p = data[li] || {};
            fila.querySelector('.f-id').value = p.id || '';
            fila.querySelector('.f-nombres').value = p.nombres || '';
            fila.querySelector('.f-apellidos').value = p.apellidos || '';
            fila.querySelector('.f-num_doc').value = p.numero_documento || '';
            fila.querySelector('.f-tipo_doc').value = p.tipo_documento || 'CI';
            fila.querySelector('.f-complemento').value = p.complemento || '';
            fila.querySelector('.f-telefono').value = p.telefono || '';
            fila.querySelector('.f-email').value = p.email || '';
            fila.querySelector('.f-direccion').value = p.direccion || '';
            fila.querySelector('.f-ocupacion').value = p.ocupacion || '';
            fila.querySelector('.f-es_tutor').checked = true;
            var campoCorreo = fila.querySelector('.campo-email-tutor');
            if (campoCorreo) campoCorreo.style.display = 'block';
            marcarTutor(fila.querySelector('.f-es_tutor'));
            fila.querySelector('.buscador-familiar').value = '';
            dd.classList.add('hidden');
            dd.innerHTML = '';
        }

        function marcarTutor(cb) {
            var fila = cb.closest('.fila-familiar');
            var cont = fila.parentNode;
            var hermanos = cont.querySelectorAll('.f-es_tutor');
            hermanos.forEach(function(h) { if (h !== cb) h.checked = false; });
            cont.querySelectorAll('.campo-email-tutor').forEach(function(c) { c.style.display = 'none'; });
            if (cb.checked) {
                var cc = fila.querySelector('.campo-email-tutor');
                if (cc) cc.style.display = 'block';
            }
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
            document.getElementById('v-email').textContent = s.email || '—';
            document.getElementById('v-idioma').textContent = s.idioma || '—';
            document.getElementById('v-cultura').textContent = s.cultura || '—';
            document.getElementById('v-discapacidad').textContent = s.discapacidad || '—';
            var pgs = s.parent_guardians || [];
            var famHtml = '';
            pgs.forEach(function(p) {
                var rol = p.pivot.tipo_relacion || 'OTRO';
                var etiqueta = ETIQUETAS_RELACION[rol] || rol;
                var esTutor = p.pivot.es_principal ? 1 : 0;
                var idTutor = 'v-persona-' + p.id;
                famHtml += '<div id="' + idTutor + '" style="border:1px solid ' + (esTutor ? 'rgba(124,58,237,0.35)' : 'var(--border)') + '; border-radius:0.5rem; padding:0.5rem 0.75rem; margin-bottom:0.5rem;' + (esTutor ? ' background-color:rgba(124,58,237,0.06)' : '') + '">' +
                    '<div class="text-xs font-semibold uppercase tracking-wider ' + (esTutor ? 'text-primary' : 'text-muted-foreground') + '">' + etiqueta + (esTutor ? ' · Tutor' : '') + '</div>' +
                    '<div class="font-medium text-foreground">' + (p.nombres || '') + ' ' + (p.apellidos || '') + '</div>' +
                    '<div class="text-xs text-muted-foreground">' + (p.numero_documento ? 'CI ' + p.numero_documento : '') + (esTutor && p.email ? ' · ' + p.email : '') + '</div>' +
                    '</div>';
            });
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
            document.getElementById('e-email_estudiante').value = s.email || '';
            document.getElementById('e-idioma').value = s.idioma || '';
            document.getElementById('e-cultura').value = s.cultura || '';
            document.getElementById('e-discapacidad').value = s.discapacidad || '';

            var cont = document.getElementById('lista-familiares-edit');
            cont.innerHTML = '';
            familiarSeqEdit = 0;
            var pgs = s.parent_guardians || [];
            pgs.forEach(function(p) {
                var idx = familiarSeqEdit;
                cont.insertAdjacentHTML('beforeend', construirFilaFamiliar(idx, p, true));
                familiarSeqEdit++;
            });
            if (pgs.length > 0) {
                cont.style.display = 'block';
                var sn = document.getElementById('sin-familiar-edit');
                if (sn) sn.style.display = 'none';
            } else {
                cont.style.display = 'none';
                var sn2 = document.getElementById('sin-familiar-edit');
                if (sn2) sn2.style.display = 'block';
            }
            renumerarFilas('lista-familiares-edit');
            openModal('modal-edit');
        }
    </script>
@endpush
