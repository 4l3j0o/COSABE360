@extends('layouts.app')
@section('titulo', 'Mi Horario')

@section('sidebar')
    @include('dashboard.partials._sidebar-estudiante')
@endsection

@section('contenido')

    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-foreground">Mi Horario</h1>
            @if($section)
                <p class="text-sm text-muted-foreground">{{ $section->codigo ?? $section->nombre }} - {{ $section->aula ?? 's/a' }}</p>
            @endif
        </div>
    </div>

    @php
        $diasLabel = [
            'LUNES' => 'Lunes',
            'MARTES' => 'Martes',
            'MIERCOLES' => 'Miércoles',
            'JUEVES' => 'Jueves',
            'VIERNES' => 'Viernes',
            'SABADO' => 'Sábado',
        ];
        $claveFranja = fn ($s) => $s->hora_inicio->format('H:i') . '-' . $s->hora_fin->format('H:i');
    @endphp

    @if($sinCurso ?? false)
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">Usted aún no tiene un curso matriculado para el período actual, por lo tanto no tiene horario.</p>
        </div>
    @elseif($schedules->isEmpty())
        <div class="bg-card border border-border rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p class="text-muted-foreground text-sm">Su curso aún no tiene horario asignado.</p>
        </div>
    @else
        @if(count($grupos) > 1)
            <div class="flex items-center gap-1.5 mb-4 bg-card border border-border rounded-xl p-1.5 w-fit shadow-sm">
                @foreach($grupos as $i => $g)
                    <button type="button" data-alu-tab="{{ $i }}"
                            class="alu-tab inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer">{{ $g['titulo'] }}</button>
                @endforeach
            </div>
        @endif
        <div id="alu-paneles">
            @foreach($grupos as $i => $g)
                <div data-alu-panel="{{ $i }}" style="display:none;">
                    @include('schedules._tabla-estudiante', ['filas' => $g['filas'], 'tabla' => $g['tabla'], 'dias' => $dias, 'diasLabel' => $diasLabel])
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        var tabs = document.querySelectorAll('[data-alu-tab]');
        var panels = document.querySelectorAll('[data-alu-panel]');
        function activar(idx) {
            tabs.forEach(function (t) {
                var on = t.getAttribute('data-alu-tab') === String(idx);
                t.className = 'alu-tab inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer ' +
                    (on ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted');
            });
            panels.forEach(function (p) { p.style.display = p.getAttribute('data-alu-panel') === String(idx) ? '' : 'none'; });
        }
        tabs.forEach(function (t) { t.addEventListener('click', function () { activar(t.getAttribute('data-alu-tab')); }); });
        activar(panels.length > 1 ? 0 : 0);
        if (panels.length === 1) { panels[0].style.display = ''; }
    })();
</script>
@endpush
