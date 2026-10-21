@extends('layouts.app')
@section('titulo', 'Licencias')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
<div class="mb-6">
    @include('layouts._volver', ['href' => route('regente.cursos'), 'texto' => 'Volver a cursos'])
    <h1 class="text-xl font-bold text-foreground mt-2">Licencias de estudiantes</h1>
    <p class="text-sm text-muted-foreground mt-1">
        Registre la licencia de un estudiante (con evidencia y comunicado automático a los docentes)
        y anúlela si es necesario. Mientras esté activa, el docente no podrá modificar la asistencia del estudiante.
    </p>
</div>

@if(session('success'))
    <div class="mb-5 px-4 py-3 rounded-lg bg-success/10 border border-success/30 text-success text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-5 px-4 py-3 rounded-lg bg-error/10 border border-error/30 text-error text-sm">{{ session('error') }}</div>
@endif

{{-- ============ FILTRO POR CURSO ============ --}}
<div class="bg-card border border-border rounded-xl p-5 mb-6 shadow-sm">
    <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3">
        <x-forms.select
            name="section_id"
            label="Curso"
            placeholder="Todos los cursos"
            :value="$section?->id"
            :options="$sections->mapWithKeys(fn ($s) => [$s->id => $s->nombreCompleto()])"
            class="w-full sm:w-96"
        />
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 cursor-pointer">Filtrar</button>
    </form>
    <p class="text-xs text-muted-foreground mt-2">Seleccione un curso para registrar licencias y ver su historial. Sin selección se muestran todas.</p>
</div>

{{-- ============ REGISTRAR LICENCIA ============ --}}
@if($section)
<div class="bg-card border border-border rounded-xl p-5 mb-6 shadow-sm">
    <h3 class="font-semibold text-foreground mb-3">Registrar licencia en {{ $section->nombreCompleto() }}</h3>

    @if($students->isEmpty())
        <p class="text-sm text-muted-foreground">El curso no tiene estudiantes matriculados activos.</p>
    @else
        <form method="POST" action="{{ route('regente.licencias.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <x-forms.select
                    name="student_id"
                    label="Estudiante"
                    required
                    :options="$students->mapWithKeys(fn ($s) => [$s->id => $s->apellidos.' '.$s->nombres])"
                />
                <x-forms.input name="fecha_inicio" type="date" label="Desde" :value="now()->toDateString()" required />
                <x-forms.input name="fecha_fin" type="date" label="Hasta" :value="now()->toDateString()" required />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                <x-forms.input name="motivo" label="Motivo" placeholder="Motivo de la licencia (se incluirá en el comunicado)" />
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600">Evidencia (foto de respaldo)</label>
                    <input type="file" name="evidencia" accept="image/*" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    @error('evidencia') <p class="text-xs text-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-6 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:opacity-90 cursor-pointer">Registrar licencia</button>
                <p class="text-xs text-muted-foreground mt-2">Solo se marcan los días con clase del rango. La licencia se aplica a todas las materias del curso.</p>
            </div>
        </form>
    @endif
</div>
@endif

{{-- ============ LISTADO DE LICENCIAS ============ --}}
<div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-5 py-4 border-b border-border">
        <h3 class="font-semibold text-foreground">Licencias registradas</h3>
    </div>
    @if($licencias->isEmpty())
        <x-ui.empty-state title="Sin licencias" description="Aún no hay licencias registradas para el filtro seleccionado." />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Curso</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Desde</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Hasta</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Motivo</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Evidencia</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                        <th class="text-right px-4 py-3 font-medium text-muted-foreground">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($licencias as $lic)
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-2 font-medium text-foreground">{{ $lic->student?->apellidos }} {{ $lic->student?->nombres }}</td>
                            <td class="px-4 py-2 text-muted-foreground">{{ $lic->section?->nombreCompleto() ?? '—' }}</td>
                            <td class="px-4 py-2 text-muted-foreground">{{ $lic->fecha_inicio?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-muted-foreground">{{ $lic->fecha_fin?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-muted-foreground">{{ $lic->motivo ?: '—' }}</td>
                            <td class="px-4 py-2">
                                @if($lic->evidencia_path)
                                    <a href="{{ asset('storage/' . $lic->evidencia_path) }}" target="_blank" class="text-primary underline text-xs">Ver foto</a>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $lic->estado === 'ACTIVA' ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground' }}">{{ $lic->estado }}</span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if($lic->estado === 'ACTIVA')
                                    <form method="POST" action="{{ route('regente.licencias.anular', $lic) }}" onsubmit="return confirm('¿Anular esta licencia? Se revertirán las asistencias y se archivará el comunicado.')" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-error/10 text-error border border-error/30 text-xs font-medium hover:bg-error hover:text-white transition-colors">Anular</button>
                                    </form>
                                @else
                                    <span class="text-xs text-muted-foreground">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
