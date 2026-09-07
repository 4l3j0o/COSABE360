@extends('layouts.app')

@section('titulo', 'RUDE - Listado')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">RUDE - Registro Único de Estudiantes</h1>
            <p class="text-sm text-muted-foreground mt-1">RM 0031/2025 — Secciones I-V — Ley 070 Art.68</p>
        </div>
        <a href="{{ route('rude.exportList') }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Exportar Listado (MinEdu)</a>
    </div>

    @if(session('success'))
        <div class="bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-card border border-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="w-full sm:flex-1 sm:min-w-[200px] min-w-0">
            <label class="text-xs font-medium text-muted-foreground">Buscar (código, nombre, CI)</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: EST-2026-0001" class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="w-full sm:w-auto">
            <label class="text-xs font-medium text-muted-foreground">Estado RUDE</label>
            <select name="estado" class="mt-1 w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="">Todos</option>
                @foreach($estados as $e)
                    <option value="{{ $e }}" {{ request('estado') === $e ? 'selected' : '' }}>{{ mb_strtolower($e) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Filtrar</button>
    </form>

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Código</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estudiante</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">CI</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">RUDE</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">SIE UE</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Estado</th>
                        <th class="text-right px-4 py-3 font-medium text-muted-foreground">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($estudiantes as $est)
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 font-mono text-xs">{{ $est->codigo }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $est->apellidos }} {{ $est->nombres }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $est->numero_documento }} {{ $est->complemento }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $est->rudeRecord?->codigo_rude ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $est->rudeRecord?->codigo_sie_ue ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php $estado = $est->rudeRecord?->estado ?? 'PENDIENTE'; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $estado === 'APROBADO' ? 'bg-success/10 text-success border-success/30' : ($estado === 'ENVIADO' ? 'bg-blue-500/10 text-blue-600 border-blue-500/30' : ($estado === 'RECHAZADO' ? 'bg-error/10 text-error border-error/30' : 'bg-warning/10 text-warning border-warning/30')) }}">{{ mb_strtolower($estado) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right flex justify-end gap-2">
                                <a href="{{ route('rude.show', $est) }}" class="px-2 py-1 border border-input rounded-lg text-xs hover:bg-accent">Ver</a>
                                <a href="{{ route('rude.form', $est) }}" class="px-2 py-1 bg-primary text-primary-foreground rounded-lg text-xs hover:bg-primary/90">Form I-V</a>
                                <a href="{{ route('rude.export', $est) }}" class="px-2 py-1 border border-input rounded-lg text-xs hover:bg-accent">Export</a>
                                <a href="{{ route('rude.export-pdf', $est) }}" class="px-2 py-1 border border-input rounded-lg text-xs hover:bg-accent">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-muted-foreground text-sm">No hay estudiantes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">{{ $estudiantes->links('pagina.custom') }}</div>
    </div>
@endsection
