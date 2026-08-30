@php
    $esEdicion = ($modo ?? 'ver') === 'editar';
    $total = $students->count();
    $conNota = $notasExistentes->filter(fn ($g) => $g->nota !== null)->count();
@endphp

@if($total > 0)
    <div class="bg-card border border-border rounded-xl overflow-hidden">
        @if(isset($mostrarResumen) && $mostrarResumen)
            <div class="flex flex-wrap gap-4 px-4 py-3 border-b border-border bg-muted/30 text-sm">
                <span class="text-muted-foreground">Estudiantes: <strong class="text-foreground">{{ $total }}</strong></span>
                <span class="text-muted-foreground">Con nota: <strong class="text-foreground">{{ $conNota }}</strong></span>
                <span class="text-muted-foreground">Sin nota: <strong class="text-foreground">{{ $total - $conNota }}</strong></span>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Código</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Estudiante</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Nota (0-100)</th>
                        <th class="text-left px-4 py-3 font-semibold text-foreground">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php $nota = $notasExistentes->get($student->id); @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 text-muted-foreground">{{ $student->codigo }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $student->apellidos }} {{ $student->nombres }}</td>
                            <td class="px-4 py-3">
                                @if($esEdicion)
                                    <input type="number" name="notas[{{ $student->id }}]" step="0.01" min="0" max="100" value="{{ $nota?->nota }}" placeholder="—"
                                        class="w-24 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-lg text-sm font-medium {{ $nota?->nota !== null ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground' }}">
                                        {{ $nota?->nota ?? '—' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($esEdicion)
                                    <input type="text" name="observaciones[{{ $student->id }}]" value="{{ $nota?->observaciones }}" placeholder="Opcional"
                                        class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @else
                                    <span class="text-muted-foreground">{{ $nota?->observaciones ?? '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="bg-card border border-border rounded-xl p-12 text-center">
        <p class="text-muted-foreground text-sm">No hay estudiantes activos registrados.</p>
    </div>
@endif
