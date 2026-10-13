@extends('layouts.app')
@section('titulo', 'Horarios de Visita')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Horarios de visita para padres</h1>
        <p class="text-sm text-muted-foreground">Días y horas en que cada docente atiende a los padres.</p>
    </div>

    <div class="bg-card border border-border rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('visitas.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Docente</label>
                <select name="teacher_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    <option value="">Todos los docentes</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ (string) ($teacherId ?? '') === (string) $t->id ? 'selected' : '' }}>{{ $t->apellidos }} {{ $t->nombres }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-success text-white">
                    <th class="px-4 py-2.5 text-left font-semibold">Docente</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Día</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Hora</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Lugar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitas as $v)
                    <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                        <td class="px-4 py-2.5 font-medium">{{ $v->teacher->apellidos }} {{ $v->teacher->nombres }}</td>
                        <td class="px-4 py-2.5">{{ \App\Models\Schedule::DIAS_LABEL[$v->dia] ?? $v->dia }}</td>
                        <td class="px-4 py-2.5 whitespace-nowrap">{{ $v->hora_inicio->format('H:i') }} – {{ $v->hora_fin->format('H:i') }}</td>
                        <td class="px-4 py-2.5">{{ $v->aula ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground text-sm">Sin horarios de visita registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $visitas->links() }}</div>
@endsection
