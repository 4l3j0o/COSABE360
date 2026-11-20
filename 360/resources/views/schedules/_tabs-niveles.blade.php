{{-- Pestañas por nivel para el horario del docente (una por nivel con clases).
     Requiere $grupos (tablasPorGrupo), $dias, $diasLabel y opcional $editable. --}}
@php $tabId = 'niv-' . uniqid(); @endphp
<div id="{{ $tabId }}">
    <div class="flex items-center gap-1.5 mb-4 bg-card border border-border rounded-xl p-1.5 w-fit shadow-sm">
        @foreach($grupos as $i => $grupo)
            @php $acc = \App\Models\Schedule::ACCENT_NIVEL[$grupo['accent']] ?? \App\Models\Schedule::ACCENT_NIVEL['primaria']; @endphp
            <button type="button"
                    data-niv-tab="{{ $i }}"
                    class="niv-tab inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer">{{ $grupo['titulo'] }}</button>
        @endforeach
    </div>

    @foreach($grupos as $i => $grupo)
        <div data-niv-panel="{{ $i }}" style="display:none;">
            @include('schedules._tabla-docente', [
                'dias' => $dias,
                'diasLabel' => $diasLabel,
                'filas' => $grupo['filas'],
                'recesos' => $grupo['recesos'],
                'tabla' => $grupo['tabla'],
                'titulo' => $grupo['titulo'],
                'accent' => $grupo['accent'],
                'editable' => $editable ?? false,
            ])
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    (function () {
        var raiz = document.getElementById('{{ $tabId }}');
        if (!raiz) return;
        var tabs = raiz.querySelectorAll('[data-niv-tab]');
        var panels = raiz.querySelectorAll('[data-niv-panel]');
        function activar(idx) {
            tabs.forEach(function (t) {
                var on = t.getAttribute('data-niv-tab') === String(idx);
                t.className = 'niv-tab inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer ' +
                    (on ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted');
            });
            panels.forEach(function (p) { p.style.display = p.getAttribute('data-niv-panel') === String(idx) ? '' : 'none'; });
            try { localStorage.setItem('horario-nivel-tab', idx); } catch (e) {}
        }
        tabs.forEach(function (t) {
            t.addEventListener('click', function () { activar(t.getAttribute('data-niv-tab')); });
        });
        var inicial = 0;
        try { var g = localStorage.getItem('horario-nivel-tab'); if (g !== null && raiz.querySelector('[data-niv-tab="' + g + '"]')) inicial = g; } catch (e) {}
        activar(inicial);
    })();
</script>
@endpush
