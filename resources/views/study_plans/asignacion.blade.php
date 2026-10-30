@extends('layouts.app')
@section('titulo', 'Asignación por Curso')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('study_plans.index'), 'texto' => 'Volver a planes'])
        <div class="mt-1">
            <h1 class="text-xl font-bold text-foreground">Asignación por Curso</h1>
            <p class="text-sm text-muted-foreground">Todos los paralelos con las materias de su plan — entra a cada uno para asignar docentes</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Paralelo</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Grado</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Plan</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Materias</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Asignadas</th>
                        <th class="text-right px-4 py-3 font-semibold text-foreground">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paralelos as $sec)
                        @php
                            $codigo = $codigos[$sec->id] ?? null;
                            $total = $codigo ? ($nMaterias[$codigo] ?? 0) : 0;
                            $hechas = $asignadas[$sec->id] ?? 0;
                        @endphp
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2.5 font-bold whitespace-nowrap">{{ $sec->codigo ?: $sec->nombre }}</td>
                            <td class="px-4 py-2.5">{{ $sec->grade?->nombre }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $codigo ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ $total }}</td>
                            <td class="px-4 py-2.5">{{ $hechas }} de {{ $total }}</td>
                            <td class="px-4 py-2.5 text-right">
                                @if($codigo)
                                    <a href="{{ route('study_plans.paralelo', $sec) }}" class="inline-block px-3 py-1.5 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:opacity-90 transition-opacity">Gestionar</a>
                                @else
                                    <span class="text-xs text-muted-foreground">Sin plan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground text-sm">No hay paralelos activos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $paralelos->links() }}</div>
@endsection
