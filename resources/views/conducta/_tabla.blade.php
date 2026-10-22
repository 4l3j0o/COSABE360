{{-- HU-82: tabla común de reportes de conducta --}}
<div class="bg-card border border-border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[820px]">
            <thead class="bg-muted/50 border-b border-border">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-muted-foreground">Fecha / Hora</th>
                    <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                    <th class="text-left px-4 py-3 font-medium text-muted-foreground">Curso</th>
                    <th class="text-left px-4 py-3 font-medium text-muted-foreground">Materia</th>
                    <th class="text-left px-4 py-3 font-medium text-muted-foreground">Descripción</th>
                    @if($mostrarAutor)
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Reportado por</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($filas as $r)
                    <tr class="hover:bg-muted/20">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $r->fecha->format('d/m/Y') }}
                            <span class="block text-xs text-muted-foreground">{{ $r->hora ? substr((string) $r->hora, 0, 5) : '—' }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium whitespace-nowrap">
                            <a href="{{ route('conducta.estudiante', ['student_id' => $r->student_id]) }}" class="hover:text-primary hover:underline">
                                {{ $r->student->apellidos }} {{ $r->student->nombres }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $r->section->codigo ?? $r->section->nombre ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $r->subject->nombre ?? 'General' }}</td>
                        <td class="px-4 py-3 max-w-[420px]">
                            <a href="{{ route('conducta.show', $r) }}" class="line-clamp-2 hover:text-primary hover:underline">{{ $r->descripcion }}</a>
                        </td>
                        @if($mostrarAutor)
                            <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {{ $r->teacher ? $r->teacher->nombres.' '.$r->teacher->apellidos : ($r->registradoPor?->nombre.' '.$r->registradoPor?->apellido ?? '—') }}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $mostrarAutor ? 6 : 5 }}" class="px-4 py-12">
                            <x-ui.empty-state title="Sin reportes de conducta" description="No hay reportes para los filtros seleccionados." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($filas, 'links'))
    <div class="mt-4">{{ $filas->links() }}</div>
@endif
