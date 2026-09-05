@extends('layouts.app')
@section('titulo', 'Reportes Académicos')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Reportes Académicos</h1>
        <p class="text-sm text-muted-foreground">Seleccione filtros y pulse el botón de descarga</p>
    </div>

    {{-- Calificaciones --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            <span class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </span>
            <div>
                <h3 class="font-semibold text-foreground">Calificaciones por Curso/Materia</h3>
                <p class="text-sm text-muted-foreground">Detalle de notas por evaluación y materia</p>
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label for="calif-gestion" class="text-xs font-medium text-muted-foreground">Gestión</label>
                <select id="calif-gestion" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label for="calif-materia" class="text-xs font-medium text-muted-foreground">Materia</label>
                <select id="calif-materia" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 ml-auto">
                <button onclick="descargarReporte('calificaciones', 'pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </button>
                <button onclick="descargarReporte('calificaciones', 'excel')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Promedios --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            <span class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </span>
            <div>
                <h3 class="font-semibold text-foreground">Promedios por Estudiante</h3>
                <p class="text-sm text-muted-foreground">Promedio general y desglose por materia</p>
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label for="prom-gestion" class="text-xs font-medium text-muted-foreground">Gestión</label>
                <select id="prom-gestion" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 ml-auto">
                <button onclick="descargarReporte('promedios', 'pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </button>
                <button onclick="descargarReporte('promedios', 'excel')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>

    <script>
        function descargarReporte(reporte, formato) {
            const routes = {
                calificaciones: '{{ route("reportes.export.academico.calificaciones") }}',
                promedios: '{{ route("reportes.export.academico.promedios") }}',
            };
            const params = new URLSearchParams();
            params.set('format', formato);

            if (reporte === 'calificaciones') {
                const gestion = document.getElementById('calif-gestion').value;
                const materia = document.getElementById('calif-materia').value;
                if (gestion) params.set('academic_year_id', gestion);
                if (materia) params.set('subject_id', materia);
            } else if (reporte === 'promedios') {
                const gestion = document.getElementById('prom-gestion').value;
                if (gestion) params.set('academic_year_id', gestion);
            }

            window.location.href = routes[reporte] + '?' + params.toString();
        }
    </script>
@endsection
