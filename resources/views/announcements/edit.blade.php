@extends('layouts.app')
@section('titulo', 'Editar comunicado')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-foreground">Editar comunicado</h1>
            <p class="text-sm text-muted-foreground mt-1">Actualice el comunicado y los destinatarios.</p>
        </div>

        @if($errors->any())
            <div class="bg-error/10 border border-error/20 text-error rounded-lg px-4 py-3 mb-4 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php $dest = $announcement->recipients->first(); @endphp

        <form method="POST" action="{{ route('comunicados.update', $announcement) }}" class="bg-card border border-border rounded-xl p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="titulo" class="block text-sm font-medium text-foreground mb-1.5">Título *</label>
                <input id="titulo" name="titulo" type="text" value="{{ old('titulo', $announcement->titulo) }}" required maxlength="200"
                       class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                @error('titulo') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="contenido" class="block text-sm font-medium text-foreground mb-1.5">Contenido *</label>
                <textarea id="contenido" name="contenido" rows="6" required
                          class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">{{ old('contenido', $announcement->contenido) }}</textarea>
                @error('contenido') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="fecha_publicacion" class="block text-sm font-medium text-foreground mb-1.5">Fecha de publicación</label>
                    <input id="fecha_publicacion" name="fecha_publicacion" type="datetime-local" value="{{ old('fecha_publicacion', $announcement->fecha_publicacion?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    @error('fecha_publicacion') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tipo_destinatario" class="block text-sm font-medium text-foreground mb-1.5">Destinatarios *</label>
                    <select id="tipo_destinatario" name="destinatarios[0][tipo_destinatario]" required
                            class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        <option value="TODOS" @selected(($dest?->tipo_destinatario ?? null) === 'TODOS')>Todos</option>
                        <option value="DOCENTES" @selected(($dest?->tipo_destinatario ?? null) === 'DOCENTES')>Docentes</option>
                        <option value="ESTUDIANTES" @selected(($dest?->tipo_destinatario ?? null) === 'ESTUDIANTES')>Estudiantes</option>
                        <option value="PADRES" @selected(($dest?->tipo_destinatario ?? null) === 'PADRES')>Padres/Tutores</option>
                        <option value="CURSO_ESPECIFICO" @selected(($dest?->tipo_destinatario ?? null) === 'CURSO_ESPECIFICO')>Curso específico</option>
                    </select>
                    @error('destinatarios.*.tipo_destinatario') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div id="campo-curso" style="display:none;">
                <label for="course_id" class="block text-sm font-medium text-foreground mb-1.5">Curso *</label>
                <select id="course_id" name="destinatarios[0][course_id]" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    <option value="">Seleccione un curso...</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(($dest?->course_id ?? null) === $course->id)>{{ $course->nombre }}</option>
                    @endforeach
                </select>
                @error('destinatarios.*.course_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
            </div>

            <div class="bg-primary/10 border border-primary/20 rounded-lg p-3 text-xs text-primary">
                El comunicado puede publicarse de inmediato o programarse con una fecha futura.
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-border">
                <a href="{{ route('comunicados.show', $announcement) }}" class="text-center px-5 py-2.5 text-sm font-medium border border-border rounded-lg hover:bg-accent transition-colors">Cancelar</a>
                <button type="submit" class="bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer">Actualizar comunicado</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const selectTipo = document.getElementById('tipo_destinatario');
        const campoCurso = document.getElementById('campo-curso');
        const selectCurso = document.getElementById('course_id');
        if (!selectTipo || !campoCurso) return;
        function toggleCurso() {
            const visible = selectTipo.value === 'CURSO_ESPECIFICO';
            campoCurso.style.display = visible ? '' : 'none';
            if (selectCurso) selectCurso.required = visible;
        }
        selectTipo.addEventListener('change', toggleCurso);
        toggleCurso();
    })();
</script>
@endpush