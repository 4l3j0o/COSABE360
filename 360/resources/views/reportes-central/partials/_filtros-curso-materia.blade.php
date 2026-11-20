{{-- Filtros en cascada: primero paralelo, luego se habilita materia.
  Vars: $cursos, $sectionSubjects, $uid (sufijo único), $form (id form o null),
        $cursoSel, $materiaSel, $compacto (una sola fila sin etiquetas) --}}
@if(! empty($compacto))
<div class="flex gap-2">
    <select name="section_id" id="curso-{{ $uid }}" @if(! empty($form)) form="{{ $form }}" @endif title="Paralelo"
        class="js-cascada-curso px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs max-w-[135px]" data-materia="materia-{{ $uid }}">
        <option value="">Paralelo…</option>
        @foreach($cursos as $section)
            <option value="{{ $section->id }}" {{ (string) $cursoSel === (string) $section->id ? 'selected' : '' }}>{{ $section->codigoCorto() }}</option>
        @endforeach
    </select>
    <select name="subject_id" id="materia-{{ $uid }}" @if(! empty($form)) form="{{ $form }}" @endif title="Materia"
        class="px-2 py-1 border border-input rounded-lg bg-background text-foreground text-xs max-w-[135px]" {{ $cursoSel ? '' : 'disabled' }}>
        <option value="">Materia…</option>
        @foreach($sectionSubjects as $cs)
            <option value="{{ $cs->subject_id }}" data-curso="{{ $cs->section_id }}" {{ (string) $materiaSel === (string) $cs->subject_id ? 'selected' : '' }}>{{ $cs->subject->nombre }}</option>
        @endforeach
    </select>
</div>
@else
<div>
    <label class="block text-xs font-semibold text-muted-foreground mb-1">Paralelo</label>
    <select name="section_id" id="curso-{{ $uid }}" @if(! empty($form)) form="{{ $form }}" @endif
        class="js-cascada-curso px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm" data-materia="materia-{{ $uid }}">
        <option value="">Seleccionar paralelo…</option>
        @foreach($cursos as $section)
            <option value="{{ $section->id }}" {{ (string) $cursoSel === (string) $section->id ? 'selected' : '' }}>{{ $section->codigoCorto() }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-muted-foreground mb-1">Materia</label>
    <select name="subject_id" id="materia-{{ $uid }}" @if(! empty($form)) form="{{ $form }}" @endif
        class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm" {{ $cursoSel ? '' : 'disabled' }}>
        <option value="">Primero elija paralelo…</option>
        @foreach($sectionSubjects as $cs)
            <option value="{{ $cs->subject_id }}" data-curso="{{ $cs->section_id }}" {{ (string) $materiaSel === (string) $cs->subject_id ? 'selected' : '' }}>{{ $cs->subject->nombre }}</option>
        @endforeach
    </select>
</div>
@endif
<script>
(function () {
    function aplicar(selCurso) {
        var selMateria = document.getElementById(selCurso.dataset.materia);
        if (!selMateria) return;
        var curso = selCurso.value;
        selMateria.querySelectorAll('option').forEach(function (o) {
            if (!o.value) { o.hidden = false; return; }
            o.hidden = !!curso && o.dataset.curso !== curso;
        });
        var visible = selMateria.querySelector('option[value="' + selMateria.value + '"]');
        if (selMateria.value && (!visible || visible.hidden)) selMateria.value = '';
        selMateria.disabled = !curso;
    }
    document.querySelectorAll('.js-cascada-curso').forEach(function (sel) {
        if (sel.dataset.cascadaLista) return;
        sel.dataset.cascadaLista = '1';
        sel.addEventListener('change', function () { aplicar(sel); });
        aplicar(sel);
    });
})();
</script>
