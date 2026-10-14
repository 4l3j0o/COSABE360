{{-- Tabla de horario docente por nivel. Grilla completa de periodos; solo se marcan los bloques del docente. --}}
@php
    $accento = \App\Models\Schedule::ACCENT_NIVEL[$accent] ?? \App\Models\Schedule::ACCENT_NIVEL['primaria'];
    $diasIso = ['LUNES' => 1, 'MARTES' => 2, 'MIERCOLES' => 3, 'JUEVES' => 4, 'VIERNES' => 5, 'SABADO' => 6];
    $hoyIso = now('America/La_Paz')->dayOfWeekIso;
    $nMarcados = collect($tabla)->flatten(1)->count();
@endphp
<div class="mt-6 first:mt-0">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <h3 class="text-sm font-bold text-foreground">{{ $titulo }}</h3>
        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $accento['chip'] }}">{{ $nMarcados }} bloque(s)</span>
    </div>
    <div class="bg-card rounded-xl overflow-hidden shadow-sm border border-border">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="{{ $accento['thead'] }} text-white">
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap w-28">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Hora
                            </span>
                        </th>
                        @foreach($dias as $dia)
                            <th class="px-3 py-3 text-center font-semibold whitespace-nowrap min-w-[10rem] border-l border-white/15 {{ ($diasIso[$dia] ?? null) === $hoyIso ? 'underline underline-offset-4 decoration-2' : '' }}">
                                {{ $diasLabel[$dia] }}
                                @if(($diasIso[$dia] ?? null) === $hoyIso)
                                    <span class="ml-1 inline-flex px-1.5 py-px rounded-full bg-white/25 text-[10px] font-bold">hoy</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $renglones = collect();
                        foreach ($filas as $fila) {
                            $renglones->push(['tipo' => 'clase', 'inicio' => $fila['inicio'], 'fin' => $fila['fin']]);
                        }
                        foreach ($recesos as $rec) {
                            $renglones->push(['tipo' => 'receso', 'inicio' => $rec['inicio'], 'fin' => $rec['fin']]);
                        }
                        $renglones = $renglones->sortBy('inicio')->values();
                    @endphp
                    @foreach($renglones as $renglon)
                        @if($renglon['tipo'] === 'receso')
                            <tr class="border-b border-border">
                                <td class="px-4 py-2 align-middle whitespace-nowrap bg-muted/40">
                                    <div class="text-sm font-bold text-foreground">{{ $renglon['inicio'] }}</div>
                                    <div class="text-[11px] text-muted-foreground">{{ $renglon['fin'] }}</div>
                                </td>
                                <td colspan="{{ count($dias) }}" class="p-2 align-top border-l border-border/60">
                                    <div class="h-full rounded-lg bg-warning/10 border border-dashed border-warning/40 px-3 py-2 text-center">
                                        <span class="text-[13px] font-bold text-warning">Receso</span>
                                    </div>
                                </td>
                            </tr>
                        @else
                        @php $clave = $renglon['inicio'] . '-' . $renglon['fin']; @endphp
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2 align-middle whitespace-nowrap bg-muted/40">
                                <div class="text-sm font-bold text-foreground">{{ $renglon['inicio'] }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ $renglon['fin'] }}</div>
                            </td>
                            @foreach($dias as $dia)
                                @php $clases = $tabla[$dia][$clave] ?? collect(); @endphp
                                <td class="p-2 align-top border-l border-border/60">
                                    @if($clases->isNotEmpty())
                                        @php $b = $clases->first(); @endphp
                                        <div class="relative h-full rounded-lg bg-card border border-border/70 px-2.5 py-2 pb-4 shadow-sm">
                                            @if($editable ?? false)
                                                <div style="position:absolute; bottom:0.375rem; right:0.375rem; display:flex; gap:0.25rem;">
                                                    <button type="button" title="Editar bloque (asignar paralelo/materia)"
                                                            onclick="editarClase({{ $b->id }}, '{{ $b->section_id ?? '' }}', '{{ $b->subject_id ?? '' }}', '{{ $b->dia }}', '{{ $b->hora_inicio->format('H:i') }}', '{{ $b->hora_fin->format('H:i') }}', '{{ addslashes($b->aula ?? '') }}')"
                                                            style="width:1.25rem; height:1.25rem; display:flex; align-items:center; justify-content:center; border-radius:0.25rem; color:var(--muted-foreground); background:transparent; border:none; cursor:pointer; padding:0;"
                                                            onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted-foreground)'">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    </button>
                                                    <form action="{{ route('schedules.destroy', $b) }}" method="POST"
                                                          onsubmit="return confirm('¿Eliminar este bloque del horario?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        @if($editable ?? false)
                                                            <input type="hidden" name="volver" value="{{ url()->current() }}">
                                                        @endif
                                                        <button type="submit" title="Eliminar bloque"
                                                                style="width:1.25rem; height:1.25rem; display:flex; align-items:center; justify-content:center; border-radius:0.25rem; color:var(--muted-foreground); background:transparent; border:none; cursor:pointer; padding:0;"
                                                                onmouseover="this.style.color='var(--error)'" onmouseout="this.style.color='var(--muted-foreground)'">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            <div class="text-[13px] font-bold leading-tight text-foreground">{{ $b->subject?->nombre ?? $b->section?->codigo ?? 'Ocupado' }}</div>
                                            @if($b->section && $b->subject)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">
                                                    {{ $b->section->codigo ?: $b->section->nombre }}
                                                </div>
                                            @elseif($b->section)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">
                                                    {{ $b->section->codigo ?: $b->section->nombre }} · sin materia
                                                </div>
                                            @elseif($editable ?? false)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">Sin paralelo — usa ✎ para detallar</div>
                                            @endif
                                            @if($b->aula)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">Aula {{ $b->aula }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="h-full min-h-[3rem] rounded-lg bg-muted/20"></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
