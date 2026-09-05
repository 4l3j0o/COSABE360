@extends('layouts.app')
@section('titulo', 'Reportes de Asistencia')

@section('sidebar')
    @php $rol = auth()->user()->rol->nombre; @endphp
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Reportes de Asistencia</h1>
        <p class="text-sm text-muted-foreground">Genere reportes por día, semana, mes o trimestre y expórtelos a PDF o Excel.</p>
    </div>

    <div class="bg-card border border-border rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('reportes.asistencia.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Rango</label>
                <select name="rango" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    @foreach(['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes', 'trimestre' => 'Trimestre'] as $val => $label)
                        <option value="{{ $val }}" {{ ($filtros['rango'] ?? 'mes') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha base</label>
                <input type="date" name="fecha" value="{{ $filtros['fecha'] ?? now()->toDateString() }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Curso</label>
                <select name="course_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todos los cursos</option>
                    @foreach($cursos as $course)
                        <option value="{{ $course->id }}" {{ ($filtros['course_id'] ?? '') == $course->id ? 'selected' : '' }}>{{ $course->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Materia</label>
                <select name="subject_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas las materias</option>
                    @if($cursos->isNotEmpty())
                        @foreach(\App\Models\CourseSubject::with('subject')->whereIn('course_id', $cursos->pluck('id'))->get()->unique('subject_id') as $cs)
                            <option value="{{ $cs->subject_id }}" {{ ($filtros['subject_id'] ?? '') == $cs->subject_id ? 'selected' : '' }}>{{ $cs->subject->nombre }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">Generar</button>
            </div>
        </form>
    </div>

    @if($resultado['filas']->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-muted-foreground text-sm">Sin registros de asistencia en el rango seleccionado.</p>
        </div>
    @else
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap gap-2 text-xs text-muted-foreground">
                <span class="inline-flex px-2.5 py-1 rounded-full bg-muted font-medium">{{ $resultado['curso'] }}</span>
                @if($resultado['materia'])
                    <span class="inline-flex px-2.5 py-1 rounded-full bg-muted font-medium">{{ $resultado['materia'] }}</span>
                @endif
                <span class="inline-flex px-2.5 py-1 rounded-full bg-primary/10 text-primary font-medium">{{ $resultado['desde'] }} → {{ $resultado['hasta'] }}</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reportes.asistencia.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                <a href="{{ route('reportes.asistencia.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-card border border-border text-foreground text-sm font-medium hover:bg-muted transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </a>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 text-left">
                            <th class="px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                            @foreach($resultado['estados'] as $estado)
                                <th class="px-4 py-3 font-semibold text-foreground">{{ ucfirst(strtolower($estado)) }}</th>
                            @endforeach
                            <th class="px-4 py-3 font-semibold text-foreground">Total</th>
                            <th class="px-4 py-3 font-semibold text-foreground">Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultado['filas'] as $fila)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 text-muted-foreground">{{ $fila['codigo'] }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $fila['nombre'] }}</td>
                                @foreach($resultado['estados'] as $estado)
                                    <td class="px-4 py-3">
                                        <span class="inline-flex min-w-[1.75rem] justify-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $fila['conteo'][$estado] > 0 ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground' }}">{{ $fila['conteo'][$estado] }}</span>
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-muted-foreground">{{ $fila['total'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 font-semibold {{ $fila['porcentaje'] >= 70 ? 'text-success' : 'text-error' }}">
                                        {{ $fila['porcentaje'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection