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
                                        <a href="{{ route('students.show', $student) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('students.edit', $student) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('¿Eliminar este estudiante?')" class="inline">
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
                {{ $students->links() }}
            </div>
        </div>

        <div class="flex justify-end mt-3">
            <a href="{{ route('students.eliminados') }}" class="text-sm text-muted-foreground hover:text-error transition-colors">
                Ver eliminados →
            </a>
        </div>
    @endif
@endsection

@push('modals')
    <div id="modal-create" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem;">
            <div style="width:100%; max-width:48rem; margin-left:auto; margin-right:auto; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
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

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
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

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
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
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo Electrónico</label>
                            <input type="email" name="correo" value="{{ old('correo') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('correo') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-muted/30 rounded-xl p-5 border border-border/50">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Información Adicional</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Idioma</label>
                            <input type="text" name="idioma" value="{{ old('idioma', 'Castellano') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Cultura</label>
                            <input type="text" name="cultura" value="{{ old('cultura') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Discapacidad</label>
                            <input type="text" name="discapacidad" value="{{ old('discapacidad') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fotografía</label>
                            <input type="file" name="fotografia" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            <p class="text-xs text-muted-foreground mt-1">Formatos: JPG, PNG. Máximo 2MB.</p>
                            @error('fotografia') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div style="position:sticky; bottom:0; display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); background:var(--card); border-radius:0 0 1rem 1rem; margin:0 -1.5rem -1.5rem; padding:1rem 1.5rem 1.5rem;">
                    <button type="button" onclick="closeModal('modal-create')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                    <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar Estudiante</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    @if(old('nombres') || $errors->any())
        <script>openModal('modal-create')</script>
    @endif
@endpush
