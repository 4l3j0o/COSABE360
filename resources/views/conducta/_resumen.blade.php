{{-- HU-82: resumen informativo (no tarjetas) --}}
<div class="bg-card border border-border rounded-xl p-5 mb-4">
    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-4 text-sm">
        <div>
            <dt class="text-muted-foreground">Reportes</dt>
            <dd class="text-lg font-semibold text-foreground">{{ $resumen['total'] }}</dd>
        </div>
        <div>
            <dt class="text-muted-foreground">Estudiantes con reportes</dt>
            <dd class="text-lg font-semibold text-foreground">{{ $resumen['estudiantes'] }}</dd>
        </div>
        <div>
            <dt class="text-muted-foreground">Último reporte</dt>
            <dd class="text-lg font-semibold text-foreground">{{ $resumen['ultima'] ? \Carbon\Carbon::parse($resumen['ultima'])->format('d/m/Y') : '—' }}</dd>
        </div>
        <div>
            <dt class="text-muted-foreground">Cursos visibles</dt>
            <dd class="text-lg font-semibold text-foreground">{{ $cursos->count() }}</dd>
        </div>
    </dl>
</div>
