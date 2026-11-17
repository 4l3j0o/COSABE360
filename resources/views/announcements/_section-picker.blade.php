@php
    $prefix = $prefix ?? 'section';
    $selectedIds = array_map('intval', (array) ($selected ?? []));
    $porNivel = $sections->groupBy(fn ($section) => $section->grade?->level?->nombre ?? 'Sin nivel');
@endphp

<div>
    <div class="flex items-center justify-between gap-3 mb-1.5">
        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600">
            Paralelos <span class="text-error">*</span>
        </label>
        <span id="{{ $prefix }}-section-count" class="text-[11px] text-muted-foreground">
            {{ count($selectedIds) }} {{ count($selectedIds) === 1 ? 'seleccionado' : 'seleccionados' }}
        </span>
    </div>

    <div class="rounded-xl border border-border bg-background overflow-hidden">
        <div class="max-h-64 overflow-y-auto p-2.5 space-y-3">
            @forelse($porNivel as $nivel => $seccionesNivel)
                <div>
                    <p class="px-1 text-[11px] font-bold uppercase tracking-wider text-primary">
                        {{ $nivel }}
                    </p>

                    @foreach($seccionesNivel->groupBy(fn ($section) => $section->grade?->nombre ?? 'Sin grado') as $grado => $seccionesGrado)
                        <div class="mt-2 first:mt-1">
                            <p class="px-1 mb-1 text-[11px] font-semibold text-muted-foreground">
                                {{ $grado }}
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($seccionesGrado as $section)
                                    <label
                                        for="{{ $prefix }}-section-{{ $section->id }}"
                                        class="flex items-center gap-2 rounded-lg border border-border bg-card px-2.5 py-2 text-xs cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-colors"
                                        title="{{ $section->codigoCompleto() }} · {{ $section->grade?->nombre }} {{ $section->nombre }}"
                                    >
                                        <input
                                            type="checkbox"
                                            id="{{ $prefix }}-section-{{ $section->id }}"
                                            name="section_ids[]"
                                            value="{{ $section->id }}"
                                            @checked(in_array((int) $section->id, $selectedIds, true))
                                            onchange="actualizarContadorSecciones('{{ $prefix }}')"
                                            class="h-3.5 w-3.5 rounded border-input accent-primary focus:ring-2 focus:ring-primary/30"
                                        >
                                        <span class="font-semibold text-foreground">{{ $section->codigoCompleto() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="p-2 text-sm text-muted-foreground">No hay paralelos disponibles.</p>
            @endforelse
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 mt-1">
        <p class="text-xs text-muted-foreground">Seleccioná uno o varios paralelos, sin usar Ctrl/Cmd.</p>
        <button type="button" onclick="limpiarSeleccionSecciones('{{ $prefix }}')" class="text-xs font-medium text-primary hover:underline">
            Limpiar
        </button>
    </div>

    @error('section_ids') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
</div>
