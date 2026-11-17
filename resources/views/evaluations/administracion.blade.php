@extends('layouts.app')
@section('titulo', 'Administrar evaluaciones')
@section('sidebar') @include('dashboard.partials._sidebar-by-rol') @endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('dashboard'), 'texto' => 'Volver al dashboard'])
    </div>

    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-foreground">Administrar evaluaciones</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Definí quiénes pueden cargar notas en cada trimestre. Los docentes no seleccionados quedan en modo lectura.
            </p>
        </div>
        <x-ui.badge color="info">{{ $planes->count() }} plan(es)</x-ui.badge>
    </div>

    <div class="flex gap-1.5 mb-5 bg-card border border-border rounded-xl p-1.5 w-fit">
        @foreach(\App\Models\EvaluationPlan::TRIMESTRES as $t)
            <a href="{{ route('evaluaciones.administracion.index', ['trimestre' => $t]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $trimestre === $t ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-muted' }}">
                {{ $t }}º trimestre
            </a>
        @endforeach
    </div>

    <form method="POST" action="{{ route('evaluaciones.administracion.update') }}" class="bg-card border border-border rounded-xl p-5 space-y-5">
        @csrf
        @method('PUT')
        <input type="hidden" name="trimestre" value="{{ $trimestre }}">

        <div>
            <h2 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3">Modo de calificación</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                @foreach($modos as $valor => $etiqueta)
                    <label class="flex items-start gap-3 rounded-xl border border-border p-4 cursor-pointer hover:bg-muted/30 transition-colors">
                        <input type="radio" name="modo" value="{{ $valor }}" @checked($setting->modo === $valor) class="mt-1 accent-primary">
                        <span>
                            <span class="block text-sm font-semibold text-foreground">
                                @if($valor === \App\Models\EvaluationPeriodSetting::GLOBAL)
                                    Todos
                                @elseif($valor === \App\Models\EvaluationPeriodSetting::SELECCIONADOS)
                                    Solo seleccionados
                                @else
                                    Solo lectura
                                @endif
                            </span>
                            <span class="block text-xs text-muted-foreground mt-1">{{ $etiqueta }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div id="docentes-seleccionados-wrap" class="{{ $setting->modo === \App\Models\EvaluationPeriodSetting::SELECCIONADOS ? '' : 'hidden' }}">
            <div class="flex items-center justify-between gap-3 mb-2">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wider">Docentes habilitados para escribir</h2>
                <span class="text-xs text-muted-foreground">Solo aplica al modo “Solo seleccionados”.</span>
            </div>

            @if($teachers->isEmpty())
                <div class="rounded-lg border border-border bg-muted/20 p-4 text-sm text-muted-foreground">
                    No hay docentes con planes de evaluación en este trimestre.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($teachers as $teacher)
                        <label class="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm cursor-pointer hover:bg-muted/30">
                            <input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}"
                                   @checked(in_array((int) $teacher->id, $seleccionados, true))
                                   class="h-4 w-4 rounded border-input accent-primary">
                            <span class="font-medium text-foreground">{{ $teacher->nombreCompleto() }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted">Cancelar</a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90">
                Guardar configuración
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const radios = document.querySelectorAll('input[name="modo"]');
                const panel = document.getElementById('docentes-seleccionados-wrap');
                if (!radios.length || !panel) return;

                function actualizarPanel() {
                    const seleccionado = document.querySelector('input[name="modo"]:checked')?.value;
                    panel.classList.toggle('hidden', seleccionado !== 'SELECCIONADOS');
                }

                radios.forEach(radio => radio.addEventListener('change', actualizarPanel));
                actualizarPanel();
            });
        </script>
    @endpush
@endsection
