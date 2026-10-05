@extends('layouts.app')
@section('titulo', 'Reportes de Conducta')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-foreground">Reportes de Conducta</h1>
            <p class="text-sm text-muted-foreground">Faltas Leves, Graves y Muy graves reportadas por curso, con fecha, hora, materia y descripción.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('conducta.estudiante') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg border border-input text-sm font-medium hover:bg-muted transition-colors">Por estudiante</a>
            @if(auth()->user()->hasAnyRole(['Docente', 'Administrador', 'Director', 'Secretaría']))
                <a href="{{ route('conducta.create', request()->only(['course_id'])) }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">+ Nuevo reporte</a>
            @endif
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('conducta.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                <select name="course_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    @if($cursos->count() !== 1)
                        <option value="">Seleccionar curso…</option>
                    @endif
                    @foreach($cursos as $course)
                        <option value="{{ $course->id }}" {{ ($filtros['course_id'] ?? '') == $course->id ? 'selected' : '' }}>{{ $course->codigoCorto() }}</option>
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
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo de falta</label>
                <select name="tipo_falta" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">Todas</option>
                    <option value="LEVE" {{ ($filtros['tipo_falta'] ?? '') === 'LEVE' ? 'selected' : '' }}>Leve</option>
                    <option value="GRAVE" {{ ($filtros['tipo_falta'] ?? '') === 'GRAVE' ? 'selected' : '' }}>Grave</option>
                    <option value="MUY_GRAVE" {{ ($filtros['tipo_falta'] ?? '') === 'MUY_GRAVE' ? 'selected' : '' }}>Muy grave</option>
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
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Generar</button>
            </div>
        </form>
    </div>

    @if($resultado['requiere_curso'])
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-foreground text-sm font-medium">Selecciona un curso para ver sus reportes de conducta.</p>
        </div>
    @elseif($resultado['filas']->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-muted-foreground text-sm">Sin reportes de conducta para este filtro.</p>
        </div>
    @else
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap gap-2 text-xs">
                @if($resultado['curso'])
                    <span class="inline-flex px-2.5 py-1 rounded-full bg-muted font-medium">{{ $resultado['curso'] }}</span>
                @endif
                @if($resultado['materia'])
                    <span class="inline-flex px-2.5 py-1 rounded-full bg-muted font-medium">{{ $resultado['materia'] }}</span>
                @endif
                @foreach(['LEVE' => 'bg-amber-100 text-amber-800', 'GRAVE' => 'bg-orange-100 text-orange-800', 'MUY_GRAVE' => 'bg-red-100 text-red-800'] as $tipo => $clase)
                    <span class="inline-flex px-2.5 py-1 rounded-full font-semibold {{ $clase }}">{{ ['LEVE' => 'Leve', 'GRAVE' => 'Grave', 'MUY_GRAVE' => 'Muy grave'][$tipo] }}: {{ $resultado['conteo'][$tipo] ?? 0 }}</span>
                @endforeach
            </div>
            <div class="flex gap-2">
                <a href="{{ route('conducta.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="px-3 py-2 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">PDF</a>
                <a href="{{ route('conducta.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="px-3 py-2 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">Excel</a>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm min-w-[820px]">
                <thead>
                    <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-neutral-500">
                        <th class="px-4 py-3">Fecha / Hora</th>
                        <th class="px-4 py-3">Estudiante</th>
                        <th class="px-4 py-3">Materia</th>
                        <th class="px-4 py-3">Falta</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3">Reportado por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultado['filas'] as $r)
                        <tr class="border-b border-border/60 hover:bg-muted/30">
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $r->fecha->format('d/m/Y') }}<br><span class="text-xs text-muted-foreground">{{ $r->hora ? substr($r->hora, 0, 5) : '—' }}</span></td>
                            <td class="px-4 py-2.5 font-medium whitespace-nowrap">
                                <a href="{{ route('conducta.estudiante', ['student_id' => $r->student_id]) }}" class="hover:text-primary hover:underline">{{ $r->student->apellidos }} {{ $r->student->nombres }}</a>
                            </td>
                            <td class="px-4 py-2.5">{{ $r->subject->nombre ?? 'General' }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $r->tipo_falta === 'LEVE' ? 'bg-amber-100 text-amber-800' : ($r->tipo_falta === 'GRAVE' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800') }}">{{ $r->tipoLabel() }}</span>
                            </td>
                            <td class="px-4 py-2.5 max-w-[320px]"><a href="{{ route('conducta.show', $r) }}" class="line-clamp-2 hover:text-primary hover:underline">{{ $r->descripcion }}</a></td>
                            <td class="px-4 py-2.5 whitespace-nowrap text-muted-foreground">{{ $r->teacher->nombres ?? '' }} {{ $r->teacher->apellidos ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
