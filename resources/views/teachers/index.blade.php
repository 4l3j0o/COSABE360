@extends('layouts.app')
@section('titulo', 'Docentes')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Docentes</h1>
            <p class="text-sm text-muted-foreground">Registro y gestión de docentes con código único</p>
        </div>
        <button onclick="openModal('modal-create-teacher')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Docente
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('teachers.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, código o documento..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>activo</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>inactivo</option>
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Buscar</button>
        </form>
    </div>

    @if($teachers->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron docentes</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Nombre</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Especialidad</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Documento</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Correo</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ $teacher->codigo }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $teacher->apellidos }} {{ $teacher->nombres }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $teacher->especialidad ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $teacher->tipo_documento }} {{ $teacher->numero_documento }}{{ $teacher->complemento ? '-' . $teacher->complemento : '' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $teacher->email ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $teacher->estaActivo() ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                                        {{ mb_strtolower($teacher->estado) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('teachers.show', $teacher) }}" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('teachers.toggle', $teacher) }}" onsubmit="return confirm('¿Seguro que deseas {{ $teacher->estaActivo() ? 'desactivar' : 'activar' }} a este docente?')" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="{{ $teacher->estaActivo() ? 'Desactivar' : 'Activar' }}">
                                                @if($teacher->estaActivo())
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este docente? Quedará oculto del listado.')" class="inline">
                                            @csrf
                                            @method('DELETE')
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
                {{ $teachers->links('pagina.custom') }}
            </div>
        </div>
    @endif
@endsection

@push('modals')
    {{-- MODAL CREAR --}}
    <div id="modal-create-teacher" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Registrar Nuevo Docente</h2>
                    <button type="button" onclick="closeModal('modal-create-teacher')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('teachers.store') }}" style="padding:1.5rem;">
                    @csrf
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                                <input type="text" name="nombres" value="{{ old('nombres') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                                <input type="text" name="apellidos" value="{{ old('apellidos') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Especialidad</label>
                                <input type="text" name="especialidad" value="{{ old('especialidad') }}" placeholder="Ej: Matemáticas" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
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
                                <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" value="{{ old('complemento') }}" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo Electrónico</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-create-teacher')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar Docente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(old('nombres') && !old('_method'))
        <script>openModal('modal-create-teacher')</script>
    @endif
@endpush
