@extends('layouts.app')
@section('titulo', 'Reportes Administrativos')

@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-foreground">Reportes Administrativos</h1>
        <p class="text-sm text-muted-foreground">Seleccione filtros y pulse el botón de descarga</p>
    </div>

    {{-- Inscripciones --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            <span class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <div>
                <h3 class="font-semibold text-foreground">Inscripciones</h3>
                <p class="text-sm text-muted-foreground">Listado de inscripciones por gestión y estado</p>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label for="insc-gestion" class="text-xs font-medium text-muted-foreground">Gestión</label>
                <select id="insc-gestion" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label for="insc-estado" class="text-xs font-medium text-muted-foreground">Estado</label>
                <select id="insc-estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todos</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="COMPLETADA">Completada</option>
                    <option value="ANULADA">Anulada</option>
                </select>
            </div>
            <div class="flex items-end gap-2 ml-auto">
                <button onclick="descargarReporte('inscripciones', 'pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </button>
                <button onclick="descargarReporte('inscripciones', 'excel')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Matrículas --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            <span class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </span>
            <div>
                <h3 class="font-semibold text-foreground">Matrículas</h3>
                <p class="text-sm text-muted-foreground">Estudiantes matriculados por curso y gestión</p>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label for="matr-gestion" class="text-xs font-medium text-muted-foreground">Gestión</label>
                <select id="matr-gestion" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label for="matr-estado" class="text-xs font-medium text-muted-foreground">Estado</label>
                <select id="matr-estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todos</option>
                    <option value="ACTIVA">Activa</option>
                    <option value="INACTIVA">Inactiva</option>
                    <option value="CANCELADA">Cancelada</option>
                </select>
            </div>
            <div class="flex items-end gap-2 ml-auto">
                <button onclick="descargarReporte('matriculas', 'pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </button>
                <button onclick="descargarReporte('matriculas', 'excel')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Pagos --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            <span class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <h3 class="font-semibold text-foreground">Pagos y Deudas</h3>
                <p class="text-sm text-muted-foreground">Estado de pagos de mensualidades</p>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label for="pagos-gestion" class="text-xs font-medium text-muted-foreground">Gestión</label>
                <select id="pagos-gestion" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todas</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label for="pagos-estado" class="text-xs font-medium text-muted-foreground">Estado</label>
                <select id="pagos-estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todos</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="PAGO_PARCIAL">Pago Parcial</option>
                    <option value="PAGADO">Pagado</option>
                    <option value="VENCIDO">Vencido</option>
                </select>
            </div>
            <div class="flex items-end gap-2 ml-auto">
                <button onclick="descargarReporte('pagos', 'pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </button>
                <button onclick="descargarReporte('pagos', 'excel')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Documentación --}}
    <div class="bg-card border border-border rounded-xl p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            <span class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <div>
                <h3 class="font-semibold text-foreground">Documentación Pendiente</h3>
                <p class="text-sm text-muted-foreground">Documentos pendientes de entrega por estudiante</p>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label for="docs-estado" class="text-xs font-medium text-muted-foreground">Estado</label>
                <select id="docs-estado" class="px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                    <option value="">Todos</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="ENTREGADO">Entregado</option>
                    <option value="VERIFICADO">Verificado</option>
                </select>
            </div>
            <div class="flex items-end gap-2 ml-auto">
                <button onclick="descargarReporte('documentacion', 'pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </button>
                <button onclick="descargarReporte('documentacion', 'excel')" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>

    <script>
        function descargarReporte(reporte, formato) {
            const routes = {
                inscripciones: '{{ route("reportes.export.administrativo.inscripciones") }}',
                matriculas: '{{ route("reportes.export.administrativo.matriculas") }}',
                pagos: '{{ route("reportes.export.administrativo.pagos") }}',
                documentacion: '{{ route("reportes.export.administrativo.documentacion") }}',
            };
            const params = new URLSearchParams();
            params.set('format', formato);

            if (reporte === 'inscripciones') {
                const gestion = document.getElementById('insc-gestion').value;
                const estado = document.getElementById('insc-estado').value;
                if (gestion) params.set('academic_year_id', gestion);
                if (estado) params.set('estado', estado);
            } else if (reporte === 'matriculas') {
                const gestion = document.getElementById('matr-gestion').value;
                const estado = document.getElementById('matr-estado').value;
                if (gestion) params.set('academic_year_id', gestion);
                if (estado) params.set('estado', estado);
            } else if (reporte === 'pagos') {
                const gestion = document.getElementById('pagos-gestion').value;
                const estado = document.getElementById('pagos-estado').value;
                if (gestion) params.set('academic_year_id', gestion);
                if (estado) params.set('estado', estado);
            } else if (reporte === 'documentacion') {
                const estado = document.getElementById('docs-estado').value;
                if (estado) params.set('estado', estado);
            }

            window.location.href = routes[reporte] + '?' + params.toString();
        }
    </script>
@endsection
