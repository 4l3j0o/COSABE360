{{-- Grilla semanal de un paralelo (estudiante). Requiere $filas, $tabla, $dias, $diasLabel. --}}
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
                @foreach($filas as $bloque)
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
                                    <span class="text-[11px] text-muted-foreground ml-1">{{ $bloque['nombre'] ?? '' }}</span>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr class="{{ !$loop->last ? 'border-b border-border' : '' }}">
                            <td class="px-4 py-2 align-middle whitespace-nowrap bg-muted/40">
                                <div class="text-sm font-bold text-foreground">{{ $bloque['inicio'] }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ $bloque['fin'] }}</div>
                            </td>
                            @foreach($dias as $dia)
                                @php $celda = ($tabla[$dia][$clave] ?? collect())->first(); @endphp
                                <td class="p-2 align-top border-l border-border/60">
                                    @if($celda)
                                        <div class="h-full rounded-lg bg-card border border-border/70 px-2.5 py-2 shadow-sm">
                                            <div class="text-[13px] font-bold leading-tight text-foreground">{{ $celda->subject?->nombre ?? '—' }}</div>
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
