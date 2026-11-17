@extends('layouts.app')
@section('titulo', 'Estado de Cuentas')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-foreground">Estado de Cuentas</h1>
        <p class="text-sm text-muted-foreground mt-1">Saldo de pensiones por estudiante. Filtre por grado y paralelo.</p>
    </div>
    <a href="{{ route('pensiones.historial-pagos') }}" class="inline-flex items-center justify-center px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Ver historial de pagos</a>
</div>

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Gestión</label>
        <select name="academic_year_id" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            @foreach($gestiones as $gestion)
                <option value="{{ $gestion->id }}" {{ (string) $filtros['academic_year_id'] === (string) $gestion->id ? 'selected' : '' }}>{{ $gestion->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Grado</label>
        <select name="grade_id" id="filtro-grado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos los grados</option>
            @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ (string) $filtros['grade_id'] === (string) $grade->id ? 'selected' : '' }}>{{ $grade->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Paralelo</label>
        <select name="section_id" id="filtro-paralelo" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos los paralelos</option>
            @foreach($secciones as $seccion)
                <option value="{{ $seccion->id }}" data-grado="{{ $seccion->grade_id }}" {{ (string) $filtros['section_id'] === (string) $seccion->id ? 'selected' : '' }}>{{ $seccion->codigoCorto() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-muted-foreground mb-1">Estado</label>
        <select name="estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos</option>
            @foreach(['AL DÍA', 'PENDIENTE', 'VENCIDO', 'SIN CUOTAS'] as $e)
                <option value="{{ $e }}" {{ $filtros['estado'] === $e ? 'selected' : '' }}>{{ ucfirst(strtolower($e)) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Filtrar</button>
    <a href="{{ route('pensiones.estado-cuentas') }}" class="px-4 py-2 border border-input rounded-lg text-sm bg-background text-foreground hover:bg-muted">Limpiar</a>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <x-ui.stat-card label="Estudiantes" :value="$resumen['estudiantes']" color="primary" />
    <x-ui.stat-card label="Total esperado (Bs)" :value="number_format($resumen['esperado'], 2)" color="info" />
    <x-ui.stat-card label="Total pagado (Bs)" :value="number_format($resumen['pagado'], 2)" color="success" />
    <x-ui.stat-card label="Saldo pendiente (Bs)" :value="number_format($resumen['saldo'], 2)" color="{{ $resumen['saldo'] > 0 ? 'error' : 'success' }}" />
</div>

@if($filas->isEmpty())
    <div class="bg-card border border-border rounded-xl p-12 text-center shadow-sm">
        <p class="font-medium text-foreground">No hay estudiantes con matrícula activa para los filtros seleccionados.</p>
        <p class="text-sm text-muted-foreground mt-1">Pruebe con otro grado o paralelo.</p>
    </div>
@else
    <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left">
                        <th class="px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Código</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Curso</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-center">Cuotas</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-right">Esperado</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-right">Pagado</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-right">Saldo</th>
                        <th class="px-4 py-3 font-semibold text-foreground">Estado</th>
                        <th class="px-4 py-3 font-semibold text-foreground text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filas as $fila)
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $fila['estudiante'] }}</td>
                            <td class="px-4 py-3 text-muted-foreground font-mono text-xs">{{ $fila['codigo'] }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $fila['curso'] }}</td>
                            <td class="px-4 py-3 text-center text-muted-foreground">
                                {{ $fila['pagadas'] }}/{{ $fila['cuotas'] }}
                                @if($fila['vencidas'] > 0)
                                    <span class="block text-[10px] text-error font-semibold">{{ $fila['vencidas'] }} vencida(s)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-foreground">{{ number_format($fila['esperado'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-success font-medium">{{ number_format($fila['pagado'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $fila['saldo'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($fila['saldo'], 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $colorEstado = match ($fila['estado']) {
                                        'AL DÍA' => 'success',
                                        'VENCIDO' => 'error',
                                        'PENDIENTE' => 'warning',
                                        default => 'neutral',
                                    };
                                @endphp
                                <x-ui.badge :color="$colorEstado">{{ ucfirst(strtolower($fila['estado'])) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('monthly-payments.estudiante', $fila['estudiante_id'] ?? 0) }}" class="text-xs font-medium text-primary hover:underline">Ver pagos</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-border">
            {{ $filas->links() }}
        </div>
    </div>
@endif

<script>
    (function () {
        var grado = document.getElementById('filtro-grado');
        var paralelo = document.getElementById('filtro-paralelo');
        if (!grado || !paralelo) return;

        function aplicar() {
            var g = grado.value;
            paralelo.querySelectorAll('option[data-grado]').forEach(function (o) {
                o.hidden = !!g && o.dataset.grado !== g;
            });
            var actual = paralelo.querySelector('option[value="' + paralelo.value + '"]');
            if (paralelo.value && actual && actual.hidden) paralelo.value = '';
        }

        grado.addEventListener('change', aplicar);
        aplicar();
    })();
</script>
@endsection
