@extends('layouts.app')
@section('titulo', 'Bitácora de Aula')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        @include('layouts._volver', ['href' => route('diario.index', ['section_id' => $entrada->section_id, 'subject_id' => $entrada->subject_id]), 'texto' => 'Volver al diario'])
        <h1 class="text-xl font-bold text-foreground mt-2">{{ $entrada->subject->nombre }} · {{ $entrada->fecha->format('d/m/Y') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $entrada->section->codigo ?? $entrada->section->nombre }} · Docente: {{ $entrada->teacher ? $entrada->teacher->nombres . ' ' . $entrada->teacher->apellidos : 'Administración' }}</p>
    </div>

    <div class="space-y-4 max-w-5xl">
        <div class="bg-card border border-border rounded-xl p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Avance curricular</h2>
            <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->avance_curricular }}</p>
        </div>

        @if($entrada->actividades->isNotEmpty())
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 pt-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600">Actividades de clases</h2>
                </div>
                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-sm min-w-[820px]">
                        <thead class="bg-muted/50 border-y border-border">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Tema</th>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Referencia</th>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Descripción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($entrada->actividades as $actividad)
                                <tr>
                                    <td class="px-4 py-3">{{ $actividad->tema_estudio ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $actividad->referencia_bibliografica ?: '—' }}</td>
                                    <td class="px-4 py-3 whitespace-pre-line">{{ $actividad->descripcion ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($entrada->tareas->isNotEmpty())
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 pt-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600">Actividades para el hogar</h2>
                </div>
                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-sm min-w-[820px]">
                        <thead class="bg-muted/50 border-y border-border">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Páginas</th>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Forma de presentación</th>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Fecha</th>
                                <th class="text-left px-4 py-2.5 font-medium text-muted-foreground">Descripción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($entrada->tareas as $tarea)
                                <tr>
                                    <td class="px-4 py-3">{{ $tarea->paginas_referencia ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $tarea->forma_presentacion ?: '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $tarea->fecha?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 whitespace-pre-line">{{ $tarea->descripcion ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($entrada->recursos->isNotEmpty())
            <div class="bg-card border border-border rounded-xl p-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-3">Recursos</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($entrada->recursos as $recurso)
                        <div class="border border-border rounded-lg p-3">
                            <p class="text-sm font-medium text-foreground">{{ $recurso->nombre ?: 'Recurso' }}</p>
                            @if($recurso->imagen_path)
                                <a href="{{ \App\Support\ArchivoPrivado::url($recurso->imagen_path) }}" target="_blank" class="block mt-2">
                                    <img src="{{ \App\Support\ArchivoPrivado::url($recurso->imagen_path) }}" alt="{{ $recurso->nombre }}" class="w-full max-h-40 object-cover rounded-md border border-border">
                                </a>
                            @endif
                            @if($recurso->link)
                                <a href="{{ $recurso->link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs text-primary hover:underline mt-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4m-4-6h4m0 0v4m0-4L10 14"/></svg>
                                    Ver enlace de referencia
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($entrada->observaciones)
            <div class="bg-card border border-border rounded-xl p-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-2">Observaciones</h2>
                <p class="text-sm text-foreground whitespace-pre-line">{{ $entrada->observaciones }}</p>
            </div>
        @endif

        @if(auth()->user()->hasAnyRole(['Administrador', 'Director']) || (auth()->user()->hasRole('Docente') && auth()->user()->teacher && $entrada->teacher_id === auth()->user()->teacher->id))
            <form method="POST" action="{{ route('diario.destroy', $entrada) }}" onsubmit="return confirm('¿Eliminar esta bitácora?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg border border-red-300 text-red-700 text-sm hover:bg-red-50 transition-colors">Eliminar bitácora</button>
            </form>
        @endif
    </div>
@endsection
