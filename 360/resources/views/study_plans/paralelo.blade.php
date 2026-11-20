@extends('layouts.app')
@section('titulo', 'Asignar Docentes - ' . ($section->codigo ?: $section->nombre))

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('study_plans.asignacion'), 'texto' => 'Volver a paralelos'])
        <div class="mt-1">
            <h1 class="text-xl font-bold text-foreground truncate">{{ $section->codigo ?: $section->nombre }} — {{ $section->grade?->nombre }}</h1>
            <p class="text-sm text-muted-foreground">Plan {{ $plan->codigo }} · {{ $materias->count() }} materias — asigna qué docente da cada materia (el horario día/hora se arma después en Horarios)</p>
        </div>
    </div>

    <form method="POST" action="{{ route('study_plans.guardar', $section) }}">
        @csrf
        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Sigla</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Materia</th>
                            <th class="text-left px-4 py-3 font-semibold text-foreground">Docente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renglones as $ren)
                            @php $m = $ren['materia']; $r = $ren['fila']; @endphp
                            <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                                <td class="px-4 py-2.5 whitespace-nowrap">{{ $m->codigo ?? '—' }}</td>
                                <td class="px-4 py-2.5 font-bold whitespace-nowrap">{{ $m->sigla ?? '—' }}</td>
                                <td class="px-4 py-2.5">{{ $m->nombre }}</td>
                                <td class="px-4 py-2.5">
                                    @if($r)
                                        <select name="filas[{{ $r->id }}]" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                                            <option value="">Sin asignar</option>
                                            @foreach($teachers as $t)
                                                <option value="{{ $t->id }}" {{ (int) $r->teacher_id === $t->id ? 'selected' : '' }}>{{ $t->apellidos }} {{ $t->nombres }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select name="nuevas[{{ $m->id }}]" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                                            <option value="">Sin asignar</option>
                                            @foreach($teachers as $t)
                                                <option value="{{ $t->id }}">{{ $t->apellidos }} {{ $t->nombres }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">Guardar Asignación</button>
        </div>
    </form>
@endsection
