@extends('layouts.app')

@section('titulo', 'Docentes')

@section('sidebar')
    @include('dashboards.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Docentes</h1>
            <p class="text-sm text-muted-foreground mt-1">Registro y gestión de docentes con código único</p>
        </div>
        <a href="{{ route('teachers.create') }}"
           class="inline-flex items-center justify-center bg-primary text-primary-foreground font-semibold py-2.5 px-5 rounded-lg hover:opacity-90 active:scale-[0.98] transition-all text-sm shadow-sm">
            + Nuevo docente
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-muted/50 text-muted-foreground border-b border-border">
                    <tr>
                        <th class="px-4 py-3 font-medium">Código</th>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Especialidad</th>
                        <th class="px-4 py-3 font-medium">Documento</th>
                        <th class="px-4 py-3 font-medium">Correo</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teachers as $teacher)
                        <tr class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-primary">{{ $teacher->codigo }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ $teacher->nombres }} {{ $teacher->apellidos }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $teacher->especialidad ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $teacher->tipo_documento }} {{ $teacher->numero_documento }}{{ $teacher->complemento ? ' ' . $teacher->complemento : '' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $teacher->correo ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $teacher->estaActivo() ? 'text-success' : 'text-error' }}">
                                    <span class="size-2 rounded-full {{ $teacher->estaActivo() ? 'bg-success' : 'bg-error' }}"></span>
                                    {{ strtolower($teacher->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('teachers.edit', $teacher) }}"
                                       class="inline-flex px-3 py-1.5 border border-border bg-card hover:bg-accent hover:text-accent-foreground rounded-lg text-xs font-medium transition-colors">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('teachers.toggle', $teacher) }}" onsubmit="return confirm('¿Seguro que deseas {{ $teacher->estaActivo() ? 'desactivar' : 'activar' }} a este docente?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors cursor-pointer {{ $teacher->estaActivo() ? 'border-error/30 text-error hover:bg-error/10' : 'border-success/30 text-success hover:bg-success/10' }}">
                                            {{ $teacher->estaActivo() ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-muted-foreground">No hay docentes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $teachers->links() }}</div>
@endsection
