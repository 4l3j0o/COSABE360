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
                                        <div class="h-full rounded-lg bg-card border border-border/70 px-2.5 py-2 shadow-sm">
                                            <div class="text-[13px] font-bold leading-tight text-foreground">{{ $clases->first()->subject?->nombre ?? $clases->first()->section?->codigo ?? 'Ocupado' }}</div>
                                            @if($clases->first()->section && $clases->first()->subject)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">
                                                    {{ $clases->first()->section->codigo ?: $clases->first()->section->nombre }}
                                                </div>
                                            @endif
                                            @if($clases->first()->aula)
                                                <div class="mt-1 text-[11px] leading-snug text-muted-foreground">Aula {{ $clases->first()->aula }}</div>
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
