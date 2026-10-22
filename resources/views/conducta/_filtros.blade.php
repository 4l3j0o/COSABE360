{{-- HU-82: filtros comunes de Conducta --}}
<form method="GET" action="{{ route('conducta.index') }}" class="bg-card border border-border rounded-xl p-4 mb-4">
    <input type="hidden" name="vista" value="{{ $vista }}">

    @if($contextoCurso)
        {{-- Curso/materia ya seleccionados: se conservan como hidden --}}
        <input type="hidden" name="contexto" value="1">
        <input type="hidden" name="section_id" value="{{ $filtros['section_id'] }}">
        <input type="hidden" name="subject_id" value="{{ $filtros['subject_id'] }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante</label>
                <select name="student_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">Todos</option>
                    @foreach($estudiantes as $e)
                        <option value="{{ $e->id }}" {{ ($filtros['student_id'] ?? '') == $e->id ? 'selected' : '' }}>{{ $e->apellidos }} {{ $e->nombres }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Desde</label>
                <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hasta</label>
                <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
                <a href="{{ route('conducta.index', ['vista' => $vista, 'section_id' => $filtros['section_id'], 'subject_id' => $filtros['subject_id'], 'contexto' => 1]) }}" class="px-3 py-2.5 rounded-lg border border-input text-sm hover:bg-muted transition-colors">Limpiar</a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                <select name="section_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">Todos</option>
                    @foreach($cursos as $section)
                        <option value="{{ $section->id }}" {{ ($filtros['section_id'] ?? '') == $section->id ? 'selected' : '' }}>{{ $section->nombreCompleto() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                <select name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">Todas</option>
                    @foreach($materias as $materia)
                        <option value="{{ $materia->id }}" {{ ($filtros['subject_id'] ?? '') == $materia->id ? 'selected' : '' }}>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante</label>
                <select name="student_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">Todos</option>
                    @foreach($estudiantes as $e)
                        <option value="{{ $e->id }}" {{ ($filtros['student_id'] ?? '') == $e->id ? 'selected' : '' }}>{{ $e->apellidos }} {{ $e->nombres }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Desde</label>
                <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hasta</label>
                <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Filtrar</button>
                <a href="{{ route('conducta.index', ['vista' => $vista]) }}" class="px-3 py-2.5 rounded-lg border border-input text-sm hover:bg-muted transition-colors">Limpiar</a>
            </div>
        </div>
    @endif
</form>
