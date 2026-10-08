@extends('layouts.app')
@section('titulo', 'Conducta por Estudiante')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('conducta.index'), 'texto' => 'Volver a Conducta'])
        <h1 class="text-xl font-bold text-foreground mt-2">Reporte de conducta por estudiante</h1>
        <p class="text-sm text-muted-foreground">Historial específico con fecha, hora, materia, tipo de falta y descripción.</p>
    </div>

    @if($estudiantes->count() !== 1)
        <div class="bg-card border border-border rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('conducta.estudiante') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante</label>
                    <select name="student_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        <option value="">Seleccionar estudiante…</option>
                        @foreach($estudiantes as $e)
                            <option value="{{ $e->id }}" {{ ($filtros['student_id'] ?? '') == $e->id ? 'selected' : '' }}>{{ $e->apellidos }} {{ $e->nombres }} ({{ $e->codigo }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Ver historial</button>
            </form>
        </div>
    @endif

    @if($student)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap gap-2 text-xs items-center">
                <span class="inline-flex px-3 py-1.5 rounded-full bg-muted font-semibold text-sm">{{ $student->apellidos }} {{ $student->nombres }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full font-semibold bg-amber-100 text-amber-800">Leves: {{ $conteo['LEVE'] }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full font-semibold bg-orange-100 text-orange-800">Graves: {{ $conteo['GRAVE'] }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full font-semibold bg-red-100 text-red-800">Muy graves: {{ $conteo['MUY_GRAVE'] }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('conducta.export', ['student_id' => $student->id, 'format' => 'pdf']) }}" class="px-3 py-2 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">PDF</a>
                <a href="{{ route('conducta.export', ['student_id' => $student->id, 'format' => 'excel']) }}" class="px-3 py-2 rounded-lg border border-input text-xs font-medium hover:bg-muted transition-colors">Excel</a>
            </div>
        </div>

        @if($reportes->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
                <p class="text-muted-foreground text-sm">Este estudiante no tiene reportes de conducta. ¡Buena conducta!</p>
            </div>
        @else
            <div class="bg-card border border-border rounded-xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-neutral-500">
                            <th class="px-4 py-3">Fecha / Hora</th>
                            <th class="px-4 py-3">Curso</th>
                            <th class="px-4 py-3">Materia</th>
                            <th class="px-4 py-3">Falta</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3">Reportado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportes as $r)
                            <tr class="border-b border-border/60 hover:bg-muted/30">
                                <td class="px-4 py-2.5 whitespace-nowrap">{{ $r->fecha->format('d/m/Y') }}<br><span class="text-xs text-muted-foreground">{{ $r->hora ? substr($r->hora, 0, 5) : '—' }}</span></td>
                                <td class="px-4 py-2.5 whitespace-nowrap">{{ $r->course->nombre }}</td>
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
    @else
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <p class="text-foreground text-sm font-medium">Selecciona un estudiante para ver su historial de conducta.</p>
        </div>
    @endif
@endsection
