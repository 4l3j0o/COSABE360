@extends('layouts.app')
@section('titulo', 'Inscripciones')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Inscripciones</h1>
            <p class="text-sm text-muted-foreground">Gestión de inscripciones por período académico</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 bg-card border border-border text-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-muted transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Ver todos los estudiantes
            </a>
            <button onclick="openModal('modal-create-enrollment')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Inscripción
            </button>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl mb-6">
        <form method="GET" action="{{ route('enrollments.index') }}" class="p-4 flex flex-col lg:flex-row gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar estudiante..." class="flex-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <select name="gestion_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los períodos</option>
                @foreach($gestiones as $g)
                    <option value="{{ $g->id }}" {{ request('gestion_id') == $g->id ? 'selected' : '' }}>{{ $g->nombre }}</option>
                @endforeach
            </select>
            <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="COMPLETADA" {{ request('estado') === 'COMPLETADA' ? 'selected' : '' }}>Completada</option>
                <option value="ANULADA" {{ request('estado') === 'ANULADA' ? 'selected' : '' }}>Anulada</option>
            </select>
            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
        </form>
    </div>

    @if($enrollments->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-muted-foreground text-sm">No se encontraron inscripciones</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estudiante</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Período</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Observaciones</th>
                            <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $enrollment->estudiante->apellidos }} {{ $enrollment->estudiante->nombres }}</div>
                                    <div class="text-xs text-muted-foreground font-mono">{{ $enrollment->estudiante->codigo }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $enrollment->gestion->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $enrollment->fecha?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    @php $colores = ['PENDIENTE'=>'bg-warning/10 text-warning','COMPLETADA'=>'bg-success/10 text-success','ANULADA'=>'bg-error/10 text-error']; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$enrollment->estado] ?? 'bg-muted text-muted-foreground' }}">{{ $enrollment->estado }}</span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground max-w-[200px] truncate">{{ $enrollment->observaciones ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button onclick="editarInscripcion({{ $enrollment->id }})" data-enrollment='@json($enrollment)' class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if($enrollment->estado !== 'ANULADA')
                                        <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}" onsubmit="return confirm('¿Anular esta inscripción?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-error/10 transition-colors text-muted-foreground hover:text-error cursor-pointer" title="Anular">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 21l-3.5-3.5M6 18L3 21l3.5-3.5"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-border">
                {{ $enrollments->links() }}
            </div>
        </div>
    @endif
@endsection

@push('modals')
    <div id="modal-create-enrollment" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:36rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Nueva Inscripción</h2>
                    <button onclick="closeModal('modal-create-enrollment')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;"><svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('enrollments.store') }}" style="padding:1.5rem;" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante <span class="text-error">*</span></label>
                        <select name="estudiante_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <option value="">Seleccionar estudiante...</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id }}" {{ old('estudiante_id') == $est->id ? 'selected' : '' }}>{{ $est->codigo }} — {{ $est->apellidos }} {{ $est->nombres }}</option>
                            @endforeach
                        </select>
                        @error('estudiante_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Período académico <span class="text-error">*</span></label>
                        <select name="gestion_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <option value="">Seleccionar período...</option>
                            @foreach($gestiones as $g)
                                <option value="{{ $g->id }}" {{ old('gestion_id') == $g->id ? 'selected' : '' }}>{{ $g->nombre }} — {{ $g->estado }}</option>
                            @endforeach
                        </select>
                        @error('gestion_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha <span class="text-error">*</span></label>
                            <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('fecha') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                            <select name="estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="PENDIENTE" {{ old('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                <option value="COMPLETADA" {{ old('estado') === 'COMPLETADA' ? 'selected' : '' }}>Completada</option>
                                <option value="ANULADA" {{ old('estado') === 'ANULADA' ? 'selected' : '' }}>Anulada</option>
                            </select>
                            @error('estado') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                        <textarea name="observaciones" rows="3" placeholder="Opcional..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('observaciones') }}</textarea>
                        @error('observaciones') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-create-enrollment')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-edit-enrollment" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:36rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Inscripción</h2>
                    <button onclick="closeModal('modal-edit-enrollment')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;"><svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form id="form-edit-enrollment" method="POST" style="padding:1.5rem;" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante <span class="text-error">*</span></label>
                        <select name="estudiante_id" id="e-estudiante_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id }}">{{ $est->codigo }} — {{ $est->apellidos }} {{ $est->nombres }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Período <span class="text-error">*</span></label>
                        <select name="gestion_id" id="e-gestion_id" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @foreach($gestiones as $g)
                                <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha <span class="text-error">*</span></label>
                            <input type="date" name="fecha" id="e-fecha" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                            <select name="estado" id="e-estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="PENDIENTE">Pendiente</option>
                                <option value="COMPLETADA">Completada</option>
                                <option value="ANULADA">Anulada</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                        <textarea name="observaciones" id="e-observaciones" rows="3" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></textarea>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="closeModal('modal-edit-enrollment')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
function editarInscripcion(id) {
    const btn = document.querySelector(`button[onclick="editarInscripcion(${id})"]`);
    const data = JSON.parse(btn.getAttribute('data-enrollment'));
    document.getElementById('e-estudiante_id').value = data.estudiante_id;
    document.getElementById('e-gestion_id').value = data.gestion_id;
    document.getElementById('e-fecha').value = data.fecha ? data.fecha.substring(0,10) : '';
    document.getElementById('e-estado').value = data.estado;
    document.getElementById('e-observaciones').value = data.observaciones || '';
    document.getElementById('form-edit-enrollment').action = `/inscripciones/${id}`;
    openModal('modal-edit-enrollment');
}
</script>
@endpush
