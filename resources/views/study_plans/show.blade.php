@extends('layouts.app')
@section('titulo', 'Plan ' . $studyPlan->codigo)

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('study_plans.index'), 'texto' => 'Volver a planes'])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-foreground truncate">{{ $studyPlan->codigo }} — {{ $studyPlan->nombre }}</h1>
                <p class="text-sm text-muted-foreground">{{ $studyPlan->level?->nombre }}{{ $studyPlan->tipo_evaluacion ? ' · ' . $studyPlan->tipo_evaluacion : '' }} — {{ $materias->count() }} materias</p>
            </div>
            <button onclick="openModal('modal-create-materia')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Agregar Materia
            </button>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Sigla</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Materia</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Área</th>
                        <th class="text-right px-4 py-3 font-semibold text-foreground">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materias as $m)
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $m->codigo ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-bold whitespace-nowrap">{{ $m->sigla ?? '—' }}</td>
                            <td class="px-4 py-2.5">@if($m->parent_id)<span class="text-muted-foreground mr-1">↳</span>@endif{{ $m->nombre }}</td>
                            <td class="px-4 py-2.5 text-muted-foreground">{{ $m->area ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" title="Editar materia"
                                            onclick="editarMateria({{ $m->id }}, '{{ addslashes($m->codigo ?? '') }}', '{{ addslashes($m->sigla ?? '') }}', '{{ addslashes($m->nombre) }}', '{{ addslashes($m->area ?? '') }}', '{{ $m->parent_id ?? '' }}')"
                                            class="px-3 py-1.5 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors cursor-pointer">Editar</button>
                                    <form method="POST" action="{{ route('study_plans.materias.destroy', [$studyPlan, $m]) }}" onsubmit="return confirm('¿Quitar {{ addslashes($m->sigla ?? $m->nombre) }} del plan {{ $studyPlan->codigo }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-300 text-red-700 text-xs font-medium hover:bg-red-50 transition-colors cursor-pointer">Quitar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground text-sm">Este plan no tiene materias.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div id="modal-create-materia" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Agregar Materia al Plan {{ $studyPlan->codigo }}</h2>
                    <button onclick="closeModal('modal-create-materia')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('study_plans.materias.store', $studyPlan) }}" style="padding:1.5rem;">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Código</label>
                            <input type="text" name="codigo" maxlength="10" placeholder="Ej: 01.01" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sigla</label>
                            <input type="text" name="sigla" maxlength="15" placeholder="Ej: LELO" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                            <p class="text-xs text-muted-foreground mt-1">Si la sigla ya existe en el nivel, solo se agrega al plan.</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia <span class="text-error">*</span></label>
                            <input type="text" name="nombre" maxlength="100" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Área</label>
                            <input type="text" name="area" maxlength="100" placeholder="Ej: COMUNIDAD Y SOCIEDAD" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia padre (subcategoría de…)</label>
                            <select name="parent_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                <option value="">Ninguna (materia principal)</option>
                                @foreach($delNivel as $p)
                                    <option value="{{ $p->id }}">{{ $p->sigla ? $p->sigla . ' · ' : '' }}{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Si eliges padre, esta materia promedia su nota en él y no sale en el boletín.</p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-create-materia')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Agregar Materia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-edit-materia" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Materia</h2>
                    <button onclick="closeModal('modal-edit-materia')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;">✕</button>
                </div>
                <form id="form-edit-materia" method="POST" style="padding:1.5rem;">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Código</label>
                            <input type="text" name="codigo" id="edit-m-codigo" maxlength="10" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sigla</label>
                            <input type="text" name="sigla" id="edit-m-sigla" maxlength="15" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia *</label>
                            <input type="text" name="nombre" id="edit-m-nombre" maxlength="100" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Área</label>
                            <input type="text" name="area" id="edit-m-area" maxlength="100" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia padre (subcategoría de…)</label>
                            <select name="parent_id" id="edit-m-parent" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                                <option value="">Ninguna (materia principal)</option>
                                @foreach($delNivel as $p)
                                    <option value="{{ $p->id }}">{{ $p->sigla ? $p->sigla . ' · ' : '' }}{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                        <button type="button" onclick="closeModal('modal-edit-materia')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function editarMateria(id, codigo, sigla, nombre, area, parentId) {
        document.getElementById('form-edit-materia').action = '{{ url('/planes-estudio/' . $studyPlan->id . '/materias') }}/' + id;
        document.getElementById('edit-m-codigo').value = codigo || '';
        document.getElementById('edit-m-sigla').value = sigla || '';
        document.getElementById('edit-m-nombre').value = nombre || '';
        document.getElementById('edit-m-area').value = area || '';
        var parentSel = document.getElementById('edit-m-parent');
        Array.from(parentSel.options).forEach(function (opt) {
            opt.disabled = opt.value !== '' && Number(opt.value) === id;
        });
        parentSel.value = parentId || '';
        openModal('modal-edit-materia');
    }
</script>
@endpush
