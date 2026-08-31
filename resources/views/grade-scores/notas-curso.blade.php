@extends('layouts.app')
@section('titulo', 'Notas por estudiante - ' . $courseSubject->subject->nombre)

@section('sidebar')
    @include('dashboard.partials._sidebar-docente')
@endsection

@section('contenido')
    @php
        $conNota = $rows->filter(fn ($r) => $r['notaFinal'] !== null)->count();
        $promedioCurso = $rows->filter(fn ($r) => $r['notaFinal'] !== null)->avg('notaFinal');
        $promedioCurso = $promedioCurso !== null ? round($promedioCurso, 2) : null;
    @endphp
    <div class="mb-6">
        <a href="{{ route('docente.evaluaciones', $courseSubject) }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a las evaluaciones del curso
        </a>
        <h1 class="text-xl font-bold text-foreground mt-2">Notas por estudiante</h1>
        <div class="mt-2 flex flex-wrap gap-2">
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $courseSubject->subject->nombre }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground">{{ $courseSubject->course->nombre }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Paralelo {{ $courseSubject->course->section->nombre ?? '—' }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">Gestión {{ $courseSubject->course->gestion->nombre ?? '—' }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="bg-card border border-border rounded-xl p-4">
            <p class="text-xs text-muted-foreground">Estudiantes</p>
            <p class="text-xl font-bold text-foreground mt-1">{{ count($students) }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-4">
            <p class="text-xs text-muted-foreground">Con nota final</p>
            <p class="text-xl font-bold text-foreground mt-1">{{ $conNota }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-4">
            <p class="text-xs text-muted-foreground">Promedio del curso</p>
            <p class="text-xl font-bold {{ ($promedioCurso ?? 0) >= 51 ? 'text-success' : 'text-warning' }} mt-1">{{ $promedioCurso ?? '—' }}</p>
        </div>
    </div>

    @if($assessments->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <p class="text-muted-foreground text-sm">Este curso aún no tiene evaluaciones registradas. Primero registre evaluaciones desde "Evaluaciones del curso".</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl p-4 mb-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 min-w-[220px]">
                    <label for="buscar-estudiante" class="text-xs font-medium text-muted-foreground">Buscar estudiante</label>
                    <input type="text" id="buscar-estudiante" placeholder="Nombre o código..."
                        class="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <button type="button" id="btn-limpiar-busqueda" class="inline-flex items-center rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground hover:bg-muted transition-colors cursor-pointer">Limpiar</button>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="text-left px-4 py-3 font-semibold text-foreground sticky left-0 bg-muted/50 min-w-[180px]">Estudiante</th>
                            @foreach($assessments as $assessment)
                                <th class="text-center px-2 py-2 font-semibold text-foreground min-w-[110px]" title="{{ $assessment->nombre }}">
                                    <div class="text-xs">{{ $assessment->nombre }}</div>
                                    <div class="text-[10px] font-normal text-muted-foreground mt-0.5">
                                        {{ $assessment->tipo }} · {{ round($assessment->peso * 100) }}%
                                    </div>
                                </th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-bold text-foreground bg-primary/10 min-w-[110px]">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 sticky left-0 bg-card">
                                    <p class="text-[10px] text-muted-foreground">{{ $row['codigo'] }}</p>
                                    <p class="font-medium text-foreground text-sm">{{ $row['apellidos'] }} {{ $row['nombres'] }}</p>
                                </td>
                                @foreach($row['porEvaluacion'] as $assessmentId => $valor)
                                    <td class="px-2 py-3 text-center">
                                        @if($valor === null)
                                            <span class="text-muted-foreground/60">—</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 rounded-lg text-sm font-medium {{ $valor >= 51 ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                                                {{ $valor }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center">
                                    @if($row['notaFinal'] === null)
                                        <span class="text-muted-foreground">—</span>
                                    @else
                                        <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-bold border {{ $row['notaFinal'] >= 51 ? 'border-success/30 bg-success/10 text-success' : 'border-error/30 bg-error/10 text-error' }}">
                                            {{ $row['notaFinal'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between gap-3 px-4 py-3 border-t border-border bg-muted/30 text-xs text-muted-foreground">
                <span>Cada celda es la nota (0-100) del estudiante en esa evaluación. La <strong class="text-foreground">Nota Final</strong> se calcula ponderando cada evaluación por su porcentaje.</span>
                <span>{{ $conNota }} de {{ count($students) }} con nota final</span>
            </div>
        </div>
    @endif
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
