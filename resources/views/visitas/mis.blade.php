@extends('layouts.app')
@section('titulo', 'Mi Horario de Visita')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Horario de visita para padres</h1>
        <p class="text-sm text-muted-foreground">
            @if(auth()->user()->hasRole('Docente'))
                Registra los días y horas en que atiendes a los padres. No puede cruzarse con tus clases.
            @else
                Gestiona el horario de visita del docente seleccionado.
            @endif
        </p>
    </div>

    @if(! auth()->user()->hasRole('Docente'))
        <div class="bg-card border border-border rounded-xl p-4 mb-4">
            <form method="GET" action="{{ route('visitas.mis') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Docente</label>
                    <select name="teacher_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacher->id == $t->id ? 'selected' : '' }}>{{ $t->apellidos }} {{ $t->nombres }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-card border border-border rounded-xl p-4 mb-6">
        <h3 class="text-sm font-semibold text-foreground mb-3">Nuevo horario de visita — {{ $teacher->nombres }} {{ $teacher->apellidos }}</h3>
        <form method="POST" action="{{ route('visitas.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @csrf
            @if(! auth()->user()->hasRole('Docente'))
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            @endif
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día *</label>
                <select name="dia" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach(\App\Models\Schedule::DIAS_LABEL as $val => $label)
                        <option value="{{ $val }}" {{ old('dia') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Desde *</label>
                <input type="time" name="hora_inicio" value="{{ old('hora_inicio') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hasta *</label>
                <input type="time" name="hora_fin" value="{{ old('hora_fin') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar</label>
                <input type="text" name="aula" value="{{ old('aula') }}" maxlength="50" placeholder="Ej: Sala de profesores" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-success text-white text-sm font-medium hover:opacity-90 transition-opacity">Agregar</button>
            </div>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-success text-white">
                    <th class="px-4 py-2.5 text-left font-semibold">Día</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Hora</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Lugar</th>
                    <th class="px-4 py-2.5 text-right font-semibold">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitas as $v)
                    <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                        <td class="px-4 py-2.5">{{ \App\Models\Schedule::DIAS_LABEL[$v->dia] ?? $v->dia }}</td>
                        <td class="px-4 py-2.5 whitespace-nowrap">{{ $v->hora_inicio->format('H:i') }} – {{ $v->hora_fin->format('H:i') }}</td>
                        <td class="px-4 py-2.5">{{ $v->aula ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" title="Editar visita"
                                        onclick="editarVisita({{ $v->id }}, '{{ $v->dia }}', '{{ $v->hora_inicio->format('H:i') }}', '{{ $v->hora_fin->format('H:i') }}', '{{ addslashes($v->aula ?? '') }}')"
                                        class="px-3 py-1.5 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">Editar</button>
                                <form method="POST" action="{{ route('visitas.destroy', $v) }}" onsubmit="return confirm('¿Eliminar este horario de visita?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-300 text-red-700 text-xs font-medium hover:bg-red-50 transition-colors">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground text-sm">Sin horarios de visita registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="modal-edit-visita" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:28rem; background:var(--card); border:1px solid var(--border); border-radius:1rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                    <h2 style="font-size:1rem; font-weight:700;">Editar visita</h2>
                    <button onclick="closeModal('modal-edit-visita')" style="background:none; border:none; cursor:pointer; color:var(--muted-foreground);">✕</button>
                </div>
                <form id="form-edit-visita" method="POST" style="padding:1.5rem;" class="grid grid-cols-2 gap-3">
                    @csrf @method('PUT')
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día *</label>
                        <select name="dia" id="edit-v-dia" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                            @foreach(\App\Models\Schedule::DIAS_LABEL as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Desde *</label>
                        <input type="time" name="hora_inicio" id="edit-v-inicio" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hasta *</label>
                        <input type="time" name="hora_fin" id="edit-v-fin" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar</label>
                        <input type="text" name="aula" id="edit-v-aula" maxlength="50" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div class="col-span-2 flex justify-end gap-2">
                        <button type="button" onclick="closeModal('modal-edit-visita')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; border:1px solid var(--border); background:var(--background); cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editarVisita(id, dia, inicio, fin, aula) {
            document.getElementById('form-edit-visita').action = '/horarios/visitas/' + id;
            document.getElementById('edit-v-dia').value = dia;
            document.getElementById('edit-v-inicio').value = inicio;
            document.getElementById('edit-v-fin').value = fin;
            document.getElementById('edit-v-aula').value = aula || '';
            openModal('modal-edit-visita');
        }
    </script>
    @endpush
@endsection
