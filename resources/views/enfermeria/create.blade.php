@extends('layouts.app')
@section('titulo', 'Nueva atención de Enfermería')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-4">
        @include('layouts._volver', ['href' => route('enfermeria.index'), 'texto' => 'Volver a Enfermería'])
    </div>

    <x-layout.page-header title="Nueva atención" description="Busque al estudiante y registre la atención básica." />

    <form method="POST" action="{{ route('enfermeria.store') }}" class="bg-card border border-border rounded-xl p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estudiante <span class="text-error">*</span></label>
            <input type="hidden" name="student_id" id="enfermeria-student-id" value="{{ old('student_id') }}">
            <input type="text" id="enfermeria-student-search" autocomplete="off" placeholder="Buscar por nombre, apellido, código o documento..."
                   class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <div id="enfermeria-student-results" class="mt-2 space-y-1"></div>
            <p id="enfermeria-student-selected" class="text-xs text-muted-foreground mt-1">
                @if(old('student_id')) Estudiante seleccionado (ID {{ old('student_id') }}) @else Ningún estudiante seleccionado @endif
            </p>
            @error('student_id') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
        </div>

        @include('enfermeria._fields', ['motivos' => $motivos])

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-border">
            <a href="{{ route('enfermeria.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">Cancelar</a>
            <button type="submit" class="bg-primary text-primary-foreground px-5 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity">Registrar atención</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('enfermeria-student-search');
        const results = document.getElementById('enfermeria-student-results');
        const hidden = document.getElementById('enfermeria-student-id');
        const selected = document.getElementById('enfermeria-student-selected');
        let timer;

        function limpiar() { results.innerHTML = ''; }

        function elegir(item) {
            hidden.value = item.id;
            selected.textContent = 'Seleccionado: ' + item.nombre + ' · ' + item.codigo + ' · ' + item.curso;
            input.value = item.nombre + ' · ' + item.codigo;
            limpiar();
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { limpiar(); return; }

            timer = setTimeout(async function () {
                try {
                    const res = await fetch('{{ route('enfermeria.buscar-estudiante') }}?q=' + encodeURIComponent(q), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    results.innerHTML = '';
                    data.forEach(function (item) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'block w-full text-left px-3 py-2 rounded-lg border border-border bg-background hover:bg-muted text-sm';
                        button.textContent = item.nombre + ' · ' + item.codigo + ' · ' + item.curso;
                        button.addEventListener('click', function () { elegir(item); });
                        results.appendChild(button);
                    });
                } catch (e) {
                    limpiar();
                }
            }, 250);
        });
    })();
</script>
@endpush
