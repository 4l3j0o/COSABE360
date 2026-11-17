@php
    $formRanking = 'form-reportes.ranking.index';
    $subjectRanking = $rankingGeneral ? '' : ($rankingMaterias->first()?->id ?? '');
@endphp

<div class="flex flex-wrap gap-1.5 max-w-[560px]">
    <select name="scope" form="{{ $formRanking }}" title="Ámbito" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs">
        <option value="global">Global</option>
        <option value="nivel">Nivel</option>
        <option value="grado">Curso/Grado</option>
        <option value="paralelo">Paralelo</option>
    </select>

    <select name="nivel_id" form="{{ $formRanking }}" title="Nivel" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs max-w-[140px]">
        <option value="">Nivel…</option>
        @foreach($rankingNiveles as $nivel)
            <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
        @endforeach
    </select>

    <select name="grado_id" form="{{ $formRanking }}" title="Curso / grado" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs max-w-[140px]">
        <option value="">Curso…</option>
        @foreach($rankingGrados as $grado)
            <option value="{{ $grado->id }}" data-level="{{ $grado->level_id }}">{{ $grado->nombre }}</option>
        @endforeach
    </select>

    <select name="section_id" form="{{ $formRanking }}" title="Paralelo" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs max-w-[150px]">
        <option value="">Paralelo…</option>
        @foreach($cursos as $section)
            <option value="{{ $section->id }}" data-grade="{{ $section->grade_id }}">{{ $section->codigoCompleto() }}</option>
        @endforeach
    </select>

    <select name="subject_id" form="{{ $formRanking }}" title="Materia" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs max-w-[150px]">
        @if($rankingGeneral)
            <option value="">Promedio general</option>
        @endif
        @foreach($rankingMaterias as $materia)
            <option value="{{ $materia->id }}" @selected((string) $subjectRanking === (string) $materia->id)>{{ $materia->nombre }}</option>
        @endforeach
    </select>

    <select name="periodo" form="{{ $formRanking }}" title="Período" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs">
        <option value="anual">Anual</option>
        <option value="1">1er trim.</option>
        <option value="2">2do trim.</option>
        <option value="3">3er trim.</option>
    </select>

    <select name="limite" form="{{ $formRanking }}" title="Cantidad" class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs">
        <option value="10">Top 10</option>
        <option value="25">Top 25</option>
        <option value="50" selected>Top 50</option>
        <option value="100">Top 100</option>
        <option value="todos">Todos</option>
    </select>
</div>
