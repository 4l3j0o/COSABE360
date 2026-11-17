@extends('layouts.app')
@section('titulo', 'Ranking de mejores promedios')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('reportes.central.index'), 'texto' => 'Volver a Central de Reportes'])
    </div>

    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-foreground">Ranking de mejores promedios</h1>
            <p class="text-sm text-muted-foreground mt-1">
                {{ $filtros['periodo_label'] }} · {{ $filtros['materia']?->nombre ?? 'Promedio general' }} · {{ $scopeLabel }}
            </p>
        </div>
        @if($resultado['filas']->isNotEmpty())
            <div class="flex gap-2">
                <a href="{{ route('reportes.ranking.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">PDF</a>
                <a href="{{ route('reportes.ranking.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Excel</a>
            </div>
        @endif
    </div>

    <form method="GET" action="{{ route('reportes.ranking.index') }}" class="bg-card border border-border rounded-xl p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Ámbito</label>
                <select name="scope" id="ranking-scope" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="global" @selected($filtros['scope'] === 'global')>Global</option>
                    <option value="nivel" @selected($filtros['scope'] === 'nivel')>Por nivel</option>
                    <option value="grado" @selected($filtros['scope'] === 'grado')>Por curso/grado</option>
                    <option value="paralelo" @selected($filtros['scope'] === 'paralelo')>Por paralelo</option>
                </select>
            </div>

            <div id="ranking-nivel-wrap">
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Nivel</label>
                <select name="nivel_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id }}" @selected((int) $filtros['nivel_id'] === (int) $nivel->id)>{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div id="ranking-grado-wrap">
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Curso / grado</label>
                <select name="grado_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach($grados as $grado)
                        <option value="{{ $grado->id }}" data-level="{{ $grado->level_id }}" @selected((int) $filtros['grado_id'] === (int) $grado->id)>{{ $grado->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div id="ranking-paralelo-wrap">
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Paralelo</label>
                <select name="section_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach($secciones as $section)
                        <option value="{{ $section->id }}" data-grade="{{ $section->grade_id }}" data-level="{{ $section->grade?->level_id }}" @selected((int) $filtros['section_id'] === (int) $section->id)>
                            {{ $section->codigoCompleto() }} — {{ $section->grade?->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Materia</label>
                <select name="subject_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @if($generalDisponible)
                        <option value="" @selected($filtros['subject_id'] === null)>Promedio general (todas las materias)</option>
                    @endif
                    @foreach($materias as $materia)
                        <option value="{{ $materia->id }}" @selected((int) $filtros['subject_id'] === (int) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Periodo</label>
                <select name="periodo" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach($periodos as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected((string) $filtros['periodo'] === (string) $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 mt-4 pt-4 border-t border-border">
            <label class="flex items-center gap-2 text-xs text-muted-foreground">
                Mostrar
                <select name="limite" class="px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach([10, 25, 50, 100] as $opcion)
                        <option value="{{ $opcion }}" @selected((string) $filtros['limite'] === (string) $opcion)>{{ $opcion }}</option>
                    @endforeach
                    <option value="todos" @selected($filtros['limite'] === null)>Todos</option>
                </select>
            </label>
            <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Ver ranking</button>
        </div>
    </form>

    @if(! $esSupervisor)
        <p class="text-xs text-muted-foreground mb-4">
            Como docente, el ranking respeta tus cursos y materias asignadas.
            @if($generalDisponible)
                Como tutor, también podés ver el promedio general y cualquier materia de tu paralelo.
            @endif
        </p>
    @endif

    @if($resultado['filas']->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="font-medium text-foreground">No hay promedios para mostrar.</p>
            <p class="text-sm text-muted-foreground mt-1">Probá cambiando el ámbito, la materia o el período.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
            <x-ui.stat-card label="Estudiantes con nota" :value="$resultado['total_filas']" color="primary" />
            <x-ui.stat-card label="Sin nota" :value="$resultado['sin_nota']" color="warning" />
            <x-ui.stat-card label="Mostrando" :value="$resultado['filas']->count()" color="info" />
        </div>

        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 text-left">
                            <th class="px-4 py-3 font-semibold text-foreground">#</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Curso</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Materia</th>
                            <th class="px-4 py-3 text-center font-semibold text-foreground">Promedio</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Valoración</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultado['filas'] as $fila)
                            @php
                                $color = match ($fila->valoracion['codigo'] ?? '') {
                                    'DP' => 'success',
                                    'DO' => 'info',
                                    'DA' => 'warning',
                                    default => 'error',
                                };
                            @endphp
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                                <td class="px-4 py-3 font-semibold text-foreground">{{ $fila->posicion }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $fila->student->codigo }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $fila->section->codigoCompleto() }} — {{ $fila->section->grade?->nombre }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $filtros['materia']?->nombre ?? 'General' }}</td>
                                <td class="px-4 py-3 text-center text-base font-bold text-foreground">{{ $fila->promedio }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :color="$color">{{ $fila->valoracion['codigo'] }} — {{ $fila->valoracion['nombre'] }}</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const scope = document.getElementById('ranking-scope');
            const nivelWrap = document.getElementById('ranking-nivel-wrap');
            const gradoWrap = document.getElementById('ranking-grado-wrap');
            const paraleloWrap = document.getElementById('ranking-paralelo-wrap');
            if (!scope) return;

            function actualizarScope() {
                if (nivelWrap) nivelWrap.style.display = scope.value === 'nivel' ? '' : 'none';
                if (gradoWrap) gradoWrap.style.display = scope.value === 'grado' ? '' : 'none';
                if (paraleloWrap) paraleloWrap.style.display = scope.value === 'paralelo' ? '' : 'none';
            }

            scope.addEventListener('change', actualizarScope);
            actualizarScope();
        })();
    </script>
@endpush
