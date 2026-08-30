@props([
    'id',
    'name',
    'items' => [],
    'placeholder' => 'Buscar...',
    'label' => null,
    'required' => false,
    'searchHint' => null,
    'especialidad' => false,
    'value' => null,
    'tope' => 5,
])

@php
    $selectedLabel = '';
    $selectedValue = $value;
    foreach ($items as $item) {
        $val = $item['value'] ?? $item->id ?? '';
        if ((string) $selectedValue === (string) $val) {
            $selectedLabel = $item['label'] ?? (isset($item->nombre) ? $item->nombre : '');
            break;
        }
    }
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="pick-{{ $id }}-input" class="block text-xs font-semibold uppercase tracking-wider text-neutral-600">
            {{ $label }} @if($required) <span class="text-error">*</span> @endif
        </label>
    @endif
    <input type="hidden" name="{{ $name }}" id="pick-{{ $id }}-value" value="{{ $selectedValue }}">
    <div id="pick-{{ $id }}" style="position:relative;">
        <input
            type="text"
            id="pick-{{ $id }}-input"
            placeholder="{{ $placeholder }}"
            value="{{ $selectedLabel }}"
            @if($selectedLabel) data-selected="1" @endif
            autocomplete="off"
            onfocus="pickAplicaFiltro('{{ $id }}')"
            oninput="pickAplicaFiltro('{{ $id }}')"
            onkeydown="pickTecla('{{ $id }}', event)"
            onblur="setTimeout(function(){ var li=document.getElementById('pick-{{ $id }}-lista'); if(li && document.activeElement !== this){ li.style.display='none'; } },120)"
            style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"
        >
        <div
            id="pick-{{ $id }}-lista"
            data-tope="{{ $tope }}"
            style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:60; max-height:220px; overflow-y:auto; background:var(--card); border:1px solid var(--border); border-radius:0.5rem; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15);"
        >
            @foreach($items as $item)
                @php
                    $valueIt = $item['value'] ?? $item->id ?? '';
                    $labelIt = $item['label'] ?? (isset($item->nombre) ? $item->nombre : (isset($item->nombres) ? trim($item->nombres.' '.($item->apellidos ?? '')) : ''));
                    $especialidadVal = $item['especialidad'] ?? ($especialidad && isset($item->especialidad) ? $item->especialidad : '');
                    $subtitulo = $item['subtitulo'] ?? ($especialidadVal ?: '');
                    $search = $item['search'] ?? strtolower($labelIt.' '.($subtitulo ?: $especialidadVal));
                @endphp
                <div
                    data-value="{{ $valueIt }}"
                    data-label="{{ $labelIt }}"
                    @if($especialidadVal) data-especialidad="{{ strtolower($especialidadVal) }}" @endif
                    data-search="{{ strtolower($search) }}"
                    onmousedown="return pickSeleccionar('{{ $id }}', this)"
                    class="pick-item px-3 py-2 cursor-pointer text-sm border-b border-border last:border-0 hover:bg-muted transition-colors"
                    style="cursor:pointer;"
                >
                    <div class="font-semibold text-foreground">{{ $labelIt }}</div>
                    @if($subtitulo)
                        <div class="text-xs text-muted-foreground">{{ $subtitulo }}</div>
                    @endif
                </div>
            @endforeach
            <div class="pick-empty" style="padding:0.625rem 0.75rem; font-size:0.875rem; color:var(--muted-foreground); display:none;">
                Sin coincidencias. Siga escribiendo...
            </div>
        </div>
    </div>
    @if($searchHint)
        <p class="text-xs text-muted-foreground">{{ $searchHint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-error">{{ $message }}</p>
    @enderror
</div>