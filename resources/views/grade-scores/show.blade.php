@extends('layouts.app')
@section('titulo', 'Registrar Calificaciones')

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    <div class="max-w-4xl">
        <div class="mb-6">
            <a href="{{ route('calificaciones.index') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a calificaciones
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Registrar Calificaciones</h1>
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $assessment->subject?->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->tipo }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('calificaciones.update', $assessment) }}" class="bg-card border border-border rounded-xl overflow-hidden">
            @csrf
            @method('PUT')

            @if($students->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-muted-foreground text-sm">No hay estudiantes activos registrados.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Estudiante</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Nota (0-100)</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php $nota = $notasExistentes->get($student->id); @endphp
                                <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 text-muted-foreground">{{ $student->codigo }}</td>
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="notas[{{ $student->id }}]" step="0.01" min="0" max="100" value="{{ $nota?->nota }}" placeholder="—"
                                            class="w-24 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="observaciones[{{ $student->id }}]" value="{{ $nota?->observaciones }}" placeholder="Opcional"
                                            class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(! $students->isEmpty())
                <div class="flex items-center justify-end gap-3 p-4 border-t border-border">
                    <a href="{{ route('calificaciones.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors">Cancelar</a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity">Guardar Calificaciones</button>
                </div>
            @endif
        </form>
    </div>
@endsection
