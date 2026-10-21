@extends('layouts.app')
@section('titulo', $announcement->titulo)

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
@php
    $coloresEstado = [
        'BORRADOR'  => 'bg-warning/10 text-warning',
        'PUBLICADO' => 'bg-success/10 text-success',
        'ARCHIVADO' => 'bg-muted text-muted-foreground',
    ];

    $etiquetasTipo = [
        'TODOS'            => 'Todos',
        'DOCENTES'         => 'Docentes',
        'ESTUDIANTES'      => 'Estudiantes',
        'CURSO_ESPECIFICO' => 'Curso específico',
        'NIVEL_DOCENTES'   => 'Docentes de un nivel',
        'DOCENTES_CURSO'   => 'Docentes de un curso',
    ];

    $rec = $announcement->recipients;
    $tipoRec = $rec->first()?->tipo_destinatario;
    $codigos = $rec->map(fn ($r) => $r->section?->codigo)->filter()->unique();
    $destino = match (true) {
        $tipoRec === 'DOCENTES_CURSO' => 'Docentes de ' . ($codigos->implode(', ') ?: '—'),
        $tipoRec === 'CURSO_ESPECIFICO' => $codigos->implode(', ') ?: '—',
        $tipoRec === 'NIVEL_DOCENTES' => 'Docentes de ' . ($rec->first()?->level?->nombre ?? 'nivel'),
        default => $etiquetasTipo[$tipoRec] ?? ($tipoRec ?? '—'),
    };

    $autor = trim(($announcement->author?->nombre ?? '') . ' ' . ($announcement->author?->apellido ?? '')) ?: 'Administración';
    $iniciales = mb_strtoupper(mb_substr($announcement->author?->nombre ?? 'A', 0, 1) . mb_substr($announcement->author?->apellido ?? '', 0, 1));
    $texto = nl2br(e($announcement->contenido));
    $texto = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $texto);
    $esLicencia = $announcement->esGeneradoPorLicencia();
@endphp
<div class="w-full">
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('announcements.index', ['tab' => 'enviados']), 'texto' => 'Volver a Mis Envíos'])
    </div>

    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-3">
                <h1 class="text-xl font-bold text-foreground leading-snug break-words">{{ $announcement->titulo }}</h1>
                <div class="shrink-0 flex flex-col items-end gap-1.5">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $coloresEstado[$announcement->estado] ?? 'bg-muted text-muted-foreground' }}">
                        {{ $announcement->estado }}
                    </span>
                    @if($esLicencia)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-primary/10 text-primary">Generado por licencia</span>
                    @endif
                    @if($announcement->destacado)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-warning/15 text-warning">Importante</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 mt-4 mb-5 pb-5 border-b border-border">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/15 text-primary text-sm font-bold">
                    {{ $iniciales ?: 'A' }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-foreground">{{ $autor }} <span class="font-normal text-muted-foreground">para {{ $destino }}</span></p>
                    <p class="text-xs text-muted-foreground">
                        {{ $announcement->fecha_publicacion?->format('d/m/Y H:i') ?? 'Sin fecha de publicación' }}
                        @if($esLicencia) · Comunicado automático de licencia @endif
                    </p>
                </div>
            </div>

            <div class="whitespace-pre-wrap break-words text-[15px] text-foreground leading-relaxed">{!! $texto !!}</div>

            @if($announcement->imagen_path)
                <img src="{{ asset('storage/' . $announcement->imagen_path) }}" alt="" class="w-full rounded-lg mt-5 border border-border">
            @endif
        </div>

        <div class="px-6 sm:px-8 py-4 border-t border-border bg-muted/20 flex flex-wrap items-center gap-2">
            @if($esLicencia)
                <p class="text-xs text-muted-foreground flex-1">
                    Este comunicado se generó automáticamente desde una licencia. Para modificarlo, gestioná la licencia en el módulo de Licencias.
                </p>
            @else
                <button type="button" onclick="openModal('modal-edit')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-medium border border-border bg-background text-foreground hover:bg-muted transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </button>

                @if($announcement->estado !== 'PUBLICADO')
                    <form method="POST" action="{{ route('announcements.publicar', $announcement) }}" onsubmit="return confirm('¿Publicar este comunicado?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="_f[tab]" value="enviados">
                        <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-medium bg-success/10 text-success hover:bg-success/20 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.5-4.5a2.5 2.5 0 013.5 3.5L13 19l-4 1 1-4 5-6z"/></svg>
                            Publicar
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('announcements.archivar', $announcement) }}" onsubmit="return confirm('¿Archivar este comunicado?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="_f[tab]" value="enviados">
                        <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-medium bg-warning/10 text-warning hover:bg-warning/20 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 012-2h10a2 2 0 012 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
                            Archivar
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('¿Eliminar este comunicado?')" class="ml-auto">
                    @csrf @method('DELETE')
                    <input type="hidden" name="_f[tab]" value="enviados">
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-medium bg-error/10 text-error hover:bg-error/20 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@if(! $esLicencia)
@push('modals')
    @include('announcements._modal-edit', ['editAnnouncement' => $announcement])
@endpush

@push('scripts')
    <script>
        function toggleSectionSelect(prefix) {
            const tipoEl = document.getElementById(prefix + '-tipo');
            if (!tipoEl) return;
            const tipo = tipoEl.value;
            const wrapSection = document.getElementById(prefix + '-section-wrap');
            const wrapLevel = document.getElementById(prefix + '-level-wrap');
            if (wrapSection) wrapSection.style.display = ['CURSO_ESPECIFICO', 'DOCENTES_CURSO'].includes(tipo) ? 'block' : 'none';
            if (wrapLevel) wrapLevel.style.display = tipo === 'NIVEL_DOCENTES' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleSectionSelect('edit');
        });
    </script>
@endpush
@endif
