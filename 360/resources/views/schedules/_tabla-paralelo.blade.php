{{-- Grilla semanal de un paralelo con editar/eliminar (vista admin por curso).
     Requiere $seccion (Section), $dias, $diasLabel, $filas (periodos), $recesos, $tabla, $subjects, $teachers. --}}
@php
    $renglones = collect();
    foreach ($filas as $f) {
        $renglones->push(['tipo' => 'clase', 'inicio' => $f['inicio'], 'fin' => $f['fin']]);
    }
    foreach ($recesos as $rec) {
        $renglones->push(['tipo' => 'receso', 'inicio' => $rec['inicio'], 'fin' => $rec['fin']]);
    }
    $renglones = $renglones->sortBy('inicio')->values();
@endphp
<div class="bg-card rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-primary-700 text-white">
                    <th class="px-4 py-3 text-left font-semibold whitespace-nowrap w-28">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Hora
                        </span>
                    </th>
                    @foreach($dias as $dia)
                        <th class="px-3 py-3 text-center font-semibold whitespace-nowrap min-w-[10rem] border-l border-white/15">{{ $diasLabel[$dia] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($renglones as $bloque)
                    @php $clave = $bloque['inicio'] . '-' . $bloque['fin']; @endphp
                    @if(($bloque['tipo'] ?? 'clase') === 'receso')
                        <tr class="border-b border-border">
                            <td class="px-4 py-2 align-middle whitespace-nowrap bg-muted/40">
                                <div class="text-sm font-bold text-foreground">{{ $bloque['inicio'] }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ $bloque['fin'] }}</div>
                            </td>
                            <td colspan="{{ count($dias) - 1 }}" class="p-2 align-top border-l border-border/60">
                                <div class="h-full rounded-lg bg-warning/10 border border-dashed border-warning/40 px-3 py-2 text-center">
                                    <span class="text-[13px] font-bold text-warning">Receso</span>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2 alignment-middle whitespace-nowrap bg-muted/40 align-middle">
                                <div class="text-sm font-bold text-foreground">{{ $bloque['inicio'] }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ $bloque['fin'] }}</div>
                            </td>
                            @foreach($dias as $dia)
                                @php $celda = ($tabla[$dia][$clave] ?? collect())->first(); @endphp
                                <td class="p-2 align-top border-l border-border/60">
                                    @if($celda)
                                        <div class="relative h-full rounded-lg bg-card border border-border/70 px-2 pt-1 pb-4 shadow-sm">
                                            <div style="position:absolute; bottom:0.375rem; right:0.375rem; display:flex; gap:0.25rem;">
                                                <button type="button" title="Editar clase"
                                                        onclick="editarClase({{ $celda->id }}, {{ $celda->subject_id }}, {{ $celda->teacher_id }}, '{{ $celda->dia }}', '{{ $celda->hora_inicio->format('H:i') }}', '{{ $celda->hora_fin->format('H:i') }}')"
                                                        style="width:1.25rem; height:1.25rem; display:flex; align-items:center; justify-content:center; border-radius:0.25rem; color:var(--muted-foreground); background:transparent; border:none; cursor:pointer; padding:0;"
                                                        onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted-foreground)'">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('schedules.destroy', $celda) }}" method="POST" onsubmit="return confirm('¿Eliminar esta clase del horario? Podrás asignar otra en este día y hora.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="volver" value="{{ url()->current() }}">
                                                    <button type="submit" title="Eliminar clase"
                                                            style="width:1.25rem; height:1.25rem; display:flex; align-items:center; justify-content:center; border-radius:0.25rem; color:var(--muted-foreground); background:transparent; border:none; cursor:pointer; padding:0;"
                                                            onmouseover="this.style.color='var(--error)'" onmouseout="this.style.color='var(--muted-foreground)'">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="text-[13px] font-bold leading-tight text-foreground">{{ $celda->subject?->nombre ?? 'Ocupado' }}</div>
                                            @if($celda->teacher)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ $celda->teacher->nombres }} {{ $celda->teacher->apellidos }}</div>
                                            @endif
                                            @if($celda->aula)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ $celda->aula }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="h-full min-h-[3.5rem] rounded-lg bg-muted/20"></div>
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
