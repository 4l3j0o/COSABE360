@extends('layouts.app')
@section('titulo', 'Mi Horario de Visita')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Horario de visita para padres</h1>
        <p class="text-sm text-muted-foreground">
            @if(auth()->user()->hasRole('Docente'))
                Registra los días y horas en que atiendes a los padres. No puede cruzarse con tus clases.
            @else
                Gestiona el horario de visita del docente seleccionado.
            @endif
        </p>
    </div>

    @if(! auth()->user()->hasRole('Docente'))
        <div class="bg-card border border-border rounded-xl p-4 mb-4">
            <form method="GET" action="{{ route('visitas.mis') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Docente</label>
                    <select name="teacher_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacher->id == $t->id ? 'selected' : '' }}>{{ $t->apellidos }} {{ $t->nombres }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-card border border-border rounded-xl p-4 mb-6">
        <h3 class="text-sm font-semibold text-foreground mb-3">Nuevo horario de visita — {{ $teacher->nombres }} {{ $teacher->apellidos }}</h3>
        <form method="POST" action="{{ route('visitas.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @csrf
            @if(! auth()->user()->hasRole('Docente'))
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            @endif
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Día *</label>
                <select name="dia" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
                    @foreach(\App\Models\Schedule::DIAS_LABEL as $val => $label)
                        <option value="{{ $val }}" {{ old('dia') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Desde *</label>
                <input type="time" name="hora_inicio" value="{{ old('hora_inicio') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Hasta *</label>
                <input type="time" name="hora_fin" value="{{ old('hora_fin') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar</label>
                <input type="text" name="aula" value="{{ old('aula') }}" maxlength="50" placeholder="Ej: Sala de profesores" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-emerald-700 text-white text-sm font-medium hover:opacity-90 transition-opacity">Agregar</button>
            </div>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-emerald-700 text-white">
                    <th class="px-4 py-2.5 text-left font-semibold">Día</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Hora</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Lugar</th>
                    <th class="px-4 py-2.5 text-right font-semibold">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitas as $v)
                    <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                        <td class="px-4 py-2.5">{{ \App\Models\Schedule::DIAS_LABEL[$v->dia] ?? $v->dia }}</td>
                        <td class="px-4 py-2.5 whitespace-nowrap">{{ $v->hora_inicio->format('H:i') }} – {{ $v->hora_fin->format('H:i') }}</td>
                        <td class="px-4 py-2.5">{{ $v->aula ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <form method="POST" action="{{ route('visitas.destroy', $v) }}" onsubmit="return confirm('¿Eliminar este horario de visita?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-300 text-red-700 text-xs font-medium hover:bg-red-50 transition-colors">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground text-sm">Sin horarios de visita registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
