@extends('layouts.app')
@section('titulo', $subject->nombre)

@section('sidebar')
    @if(auth()->user()->hasRole('Director'))
        @include('dashboard.partials._sidebar-director')
    @else
        @include('dashboard.partials._sidebar-admin')
    @endif
@endsection

@section('contenido')
    @php
        $coloresEstado = [
            'ACTIVO'    => 'bg-success/10 text-success',
            'INACTIVO'  => 'bg-warning/10 text-warning',
            'ELIMINADO' => 'bg-error/10 text-error',
        ];
    @endphp
    <div class="mb-6">
        @include('layouts._volver', ['href' => request('volver') && str_starts_with(request('volver'), url('/')) ? request('volver') : route('subjects.index'), 'texto' => 'Volver al listado'])
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
            <h1 class="text-xl font-bold text-foreground">{{ $subject->nombre }}</h1>
            @if($subject->estado !== 'ELIMINADO' && auth()->user()->esAdmin())
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-editar-materia" onclick="activarEdicionMateria()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </button>
                    <button type="button" id="btn-cancelar-edicion-materia" onclick="cancelarEdicionMateria()" style="display:none;" class="inline-flex items-center gap-2 bg-muted text-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                        Cancelar
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div id="info-materia">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos de la Materia</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Nivel Educativo</dt>
                            <dd class="text-foreground font-medium">{{ $subject->level?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Nombre</dt>
                            <dd class="text-foreground font-medium">{{ $subject->nombre }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-muted-foreground">Descripción</dt>
                            <dd class="text-foreground">{{ $subject->descripcion ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Horas Semanales</dt>
                            <dd class="text-foreground">{{ $subject->horas_semanales ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Estado</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $coloresEstado[$subject->estado] ?? 'bg-muted text-muted-foreground' }}">{{ mb_strtolower($subject->estado) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Registro</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Creado</dt>
                            <dd class="text-foreground">{{ $subject->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Actualizado</dt>
                            <dd class="text-foreground">{{ $subject->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <form id="form-editar-materia" method="POST" action="{{ route('subjects.update', $subject) }}" style="display:none;">
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
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-4">Datos de la Materia</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-forms.picker
                                id="level-edit"
                                name="level_id"
                                label="Nivel Educativo"
                                required
                                placeholder="Buscar nivel..."
                                value="{{ old('level_id', $subject->level_id) }}"
                                :items="$levels->map(fn($l) => ['value' => $l->id, 'label' => $l->nombre])"
                            />
                            @error('level_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre de la Materia <span class="text-error">*</span></label>
                            <input type="text" name="nombre" class="{{ $claseInput }}" value="{{ old('nombre', $subject->nombre) }}" required maxlength="100">
                            @error('nombre') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Descripción</label>
                            <textarea name="descripcion" class="{{ $claseInput }}" maxlength="255" rows="2">{{ old('descripcion', $subject->descripcion) }}</textarea>
                            @error('descripcion') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Horas Semanales</label>
                            <input type="number" name="horas_semanales" class="{{ $claseInput }}" value="{{ old('horas_semanales', $subject->horas_semanales) }}" min="0" max="255">
                            @error('horas_semanales') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                            <select name="estado" class="{{ $claseInput }}">
                                <option value="ACTIVO" @selected(old('estado', $subject->estado) === 'ACTIVO')>Activo</option>
                                <option value="INACTIVO" @selected(old('estado', $subject->estado) === 'INACTIVO')>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-card border border-border rounded-xl p-6 flex items-center justify-end gap-3">
            <button type="button" onclick="cancelarEdicionMateria()" class="px-4 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border-none bg-primary text-primary-foreground hover:opacity-90 transition-opacity cursor-pointer">Guardar Cambios</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function activarEdicionMateria() {
            document.getElementById('info-materia').style.display = 'none';
            document.getElementById('form-editar-materia').style.display = 'block';
            document.getElementById('btn-editar-materia').style.display = 'none';
            document.getElementById('btn-cancelar-edicion-materia').style.display = 'inline-flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicionMateria() {
            document.getElementById('info-materia').style.display = 'block';
            document.getElementById('form-editar-materia').style.display = 'none';
            document.getElementById('btn-editar-materia').style.display = 'inline-flex';
            document.getElementById('btn-cancelar-edicion-materia').style.display = 'none';
        }

        @if($errors->any())
            activarEdicionMateria();
        @endif
    </script>
@endpush
