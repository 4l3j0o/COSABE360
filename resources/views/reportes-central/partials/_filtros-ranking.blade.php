@php
    $formRanking = 'form-reportes.ranking.index';
    $subjectRanking = $rankingGeneral ? '' : ($rankingMaterias->first()?->id ?? '');
@endphp

<div>
    <button
        type="button"
        onclick="document.getElementById('central-ranking-filtros').classList.toggle('hidden')"
        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-input bg-background text-xs font-medium text-foreground hover:bg-muted transition-colors"
    >
        <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4"/>
        </svg>
        Filtros
        <span class="text-muted-foreground font-normal">Global · Anual · Top 50</span>
        <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div id="central-ranking-filtros" class="hidden mt-2 w-[360px] max-w-full rounded-xl border border-border bg-muted/20 p-3">
        <div class="grid grid-cols-2 gap-2.5">
            <label class="block">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Ámbito</span>
                <select id="central-ranking-scope" name="scope" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    <option value="global">Global</option>
                    <option value="nivel">Por nivel</option>
                    <option value="grado">Por curso/grado</option>
                    <option value="paralelo">Por paralelo</option>
                </select>
            </label>

            <label class="block">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Materia</span>
                <select name="subject_id" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    @if($rankingGeneral)
                        <option value="">Promedio general</option>
                    @endif
                    @foreach($rankingMaterias as $materia)
                        <option value="{{ $materia->id }}" @selected((string) $subjectRanking === (string) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </label>

            <label id="central-ranking-nivel-wrap" class="block hidden">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Nivel</span>
                <select name="nivel_id" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    @foreach($rankingNiveles as $nivel)
                        <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
            </label>

            <label id="central-ranking-grado-wrap" class="block hidden">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Curso / grado</span>
                <select name="grado_id" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    @foreach($rankingGrados as $grado)
                        <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                    @endforeach
                </select>
            </label>

            <label id="central-ranking-paralelo-wrap" class="block hidden">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Paralelo</span>
                <select name="section_id" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    @foreach($cursos as $section)
                        <option value="{{ $section->id }}">{{ $section->codigoCompleto() }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Período</span>
                <select name="periodo" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    <option value="anual">Anual</option>
                    <option value="1">1er trimestre</option>
                    <option value="2">2do trimestre</option>
                    <option value="3">3er trimestre</option>
                </select>
            </label>

            <label class="block">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Cantidad</span>
                <select name="limite" form="{{ $formRanking }}" class="w-full px-2 py-1.5 border border-input rounded-lg bg-background text-foreground text-xs">
                    <option value="10">Top 10</option>
                    <option value="25">Top 25</option>
                    <option value="50" selected>Top 50</option>
                    <option value="100">Top 100</option>
                    <option value="todos">Todos</option>
                </select>
            </label>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const scope = document.getElementById('central-ranking-scope');
        if (!scope) return;

        const campos = {
            nivel: document.getElementById('central-ranking-nivel-wrap'),
            grado: document.getElementById('central-ranking-grado-wrap'),
            paralelo: document.getElementById('central-ranking-paralelo-wrap'),
        };

        function actualizarCamposRanking() {
            Object.entries(campos).forEach(function ([nombre, campo]) {
                if (!campo) return;
                campo.classList.toggle('hidden', scope.value !== nombre);
            });
        }

        scope.addEventListener('change', actualizarCamposRanking);
        actualizarCamposRanking();
    })();
</script>
@endpush
