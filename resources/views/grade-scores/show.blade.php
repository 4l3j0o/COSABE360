@extends('layouts.app')
@section('titulo', 'Registrar Calificaciones')

@section('sidebar')
    @if(auth()->user()->hasRole('Administrador'))
        @include('dashboard.partials._sidebar-by-rol')
    @else
        @include('dashboard.partials._sidebar-docente')
    @endif
@endsection

@section('contenido')
    <div class="max-w-4xl">
        <div class="mb-6">
            @php $cursoEvaluacion = $assessment->courseSubject?->course; @endphp
            <a href="{{ $volver ?? ($cursoEvaluacion ? route('courses.calificaciones', $cursoEvaluacion) : route('asistencias.index')) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                @if(isset($volver))
                    Volver a las evaluaciones del curso
                @elseif($cursoEvaluacion)
                    Volver a Calificaciones - {{ $cursoEvaluacion->nombre }}
                @else
                    Volver a calificaciones
                @endif
            </a>
            <h1 class="text-xl font-bold text-foreground mt-2">Registrar Calificaciones</h1>
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $assessment->subject?->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->nombre }}</span>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">{{ $assessment->tipo }}</span>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-4 mb-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 min-w-[220px]">
                    <label for="buscar-estudiante" class="text-xs font-medium text-muted-foreground">Buscar estudiante</label>
                    <input type="text" id="buscar-estudiante" placeholder="Nombre o código..."
                        class="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <button type="button" id="btn-limpiar-busqueda" class="inline-flex items-center rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground hover:bg-muted transition-colors">
                    Limpiar
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('calificaciones.update', $assessment) }}" class="bg-card border border-border rounded-xl overflow-hidden">
            @csrf
            @method('PUT')
            @if(isset($volverCurso))
                <input type="hidden" name="volver_curso" value="{{ $volverCurso }}">
            @endif

            @include('grade-scores.partial-tabla-notas', ['modo' => 'editar'])

            @if(! $students->isEmpty())
                <div class="flex items-center justify-end gap-3 p-4 border-t border-border">
                    <a href="{{ $cursoEvaluacion ? route('courses.calificaciones', $cursoEvaluacion) : route('asistencias.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors">Cancelar</a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 transition-opacity">Guardar Calificaciones</button>
                </div>
            @endif
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('buscar-estudiante');
        const limpiar = document.getElementById('btn-limpiar-busqueda');
        if (!input || !limpiar) return;

        const filas = document.querySelectorAll('table tbody tr');

        function aplicar() {
            const termino = input.value.trim().toLowerCase();
            filas.forEach(function (fila) {
                const texto = fila.textContent.toLowerCase();
                const visible = !termino || texto.includes(termino);
                fila.style.display = visible ? '' : 'none';
            });
        }

        input.addEventListener('input', aplicar);
        limpiar.addEventListener('click', function () {
            input.value = '';
            aplicar();
        });
    })();
</script>
@endpush