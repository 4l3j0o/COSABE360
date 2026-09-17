@extends('layouts.app')
@section('titulo', 'Tipos de Descuento')
@section('sidebar') @include('dashboard.partials._sidebar-admin') @endsection
@section('contenido')
@php $R = \App\Models\DiscountType::class; @endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold">Tipos de Descuento / Becas</h1>
        <p class="text-sm text-muted-foreground mt-1">Mensualidad base: Bs 785.50</p>
    </div>
    <button onclick="abrirModal()" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nuevo tipo
    </button>
</div>


@if($errors->any())
    <div class="bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm mb-4"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="GET" class="bg-card border border-border rounded-xl p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="text-xs font-medium text-muted-foreground">Buscar</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Código o nombre..." class="w-full mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
    </div>
    <div>
        <label class="text-xs font-medium text-muted-foreground">Estado</label>
        <select name="estado" class="mt-1 px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
            <option value="">Todos</option>
            <option value="ACTIVO" {{ request('estado')==='ACTIVO'?'selected':'' }}>Activo</option>
            <option value="INACTIVO" {{ request('estado')==='INACTIVO'?'selected':'' }}>Inactivo</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm hover:bg-primary/90">Filtrar</button>
</form>

<div class="bg-card border border-border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50">
                <th class="px-4 py-3 text-left">Código</th>
                <th class="px-4 py-3 text-left">Nombre</th>
                <th class="px-4 py-3">Aplicación</th>
                <th class="px-4 py-3">Porcentaje</th>
                <th class="px-4 py-3">Importe Fijo</th>
                <th class="px-4 py-3">Orden</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3 text-right">Acción</th>
            </tr></thead>
            <tbody>
                @forelse($tipos as $t)
                <tr class="border-b hover:bg-muted/20">
                    <td class="px-4 py-3 font-mono text-xs">{{ $t->codigo }}</td>
                    <td class="px-4 py-3">{{ $t->nombre }}</td>
                    <td class="px-4 py-3 text-center text-xs">{{ $R::APLICACIONES[$t->tipo_aplicacion] ?? $t->tipo_aplicacion }}</td>
                    <td class="px-4 py-3 text-center">{{ $t->porcentaje !== null ? $t->porcentaje.'%' : '—' }}</td>
                    <td class="px-4 py-3 text-center">{{ $t->importe_fijo !== null ? 'Bs '.number_format((float)$t->importe_fijo,2) : '—' }}</td>
                    <td class="px-4 py-3 text-center">{{ $t->orden }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded text-xs {{ $t->estado==='ACTIVO'?'bg-success/20 text-success':'bg-muted text-muted-foreground' }}">{{ $t->estado }}</span>
                    </td>
                    <td class="px-4 py-3 text-right flex justify-end gap-1">
                        <button onclick='abrirModal(@json($t))' class="px-2 py-1 border border-input rounded-lg text-xs hover:bg-accent cursor-pointer">Editar</button>
                        <form method="POST" action="{{ route('discount-types.destroy', $t) }}" onsubmit="return confirm('¿Eliminar este tipo de descuento?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2 py-1 border border-error/30 text-error rounded-lg text-xs hover:bg-error/10 cursor-pointer">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty <tr><td colspan="8" class="px-4 py-12 text-center text-muted-foreground">Sin tipos de descuento registrados.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-border">{{ $tipos->links() }}</div>
</div>

<div id="modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px);">
    <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
        <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
                <h2 id="modal-titulo" style="font-size:1rem; font-weight:700;">Nuevo tipo de descuento</h2>
                <button onclick="cerrarModal()" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;">✕</button>
            </div>
            <form id="modal-form" method="POST" action="{{ route('discount-types.store') }}" style="padding:1.5rem;">
                @csrf
                <input type="hidden" name="_method" id="campo-method" value="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1">Código <span class="text-error">*</span></label>
                        <input type="text" name="codigo" id="campo-codigo" required maxlength="20" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1">Nombre <span class="text-error">*</span></label>
                        <input type="text" name="nombre" id="campo-nombre" required maxlength="200" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1">Tipo de aplicación <span class="text-error">*</span></label>
                        <select name="tipo_aplicacion" id="campo-tipo" required class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                            @foreach($R::APLICACIONES as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted-foreground mb-1">Porcentaje (%)</label>
                            <input type="number" name="porcentaje" id="campo-porcentaje" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted-foreground mb-1">Importe Fijo</label>
                            <input type="number" name="importe_fijo" id="campo-importe" step="0.01" min="0" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1">Orden <span class="text-error">*</span></label>
                        <input type="number" name="orden" id="campo-orden" required min="1" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1">Estado</label>
                        <select name="estado" id="campo-estado" class="w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm">
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="INACTIVO">INACTIVO</option>
                        </select>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <button type="button" onclick="cerrarModal()" class="px-4 py-2 rounded-lg text-sm border border-border bg-background text-foreground hover:bg-muted cursor-pointer">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-primary text-primary-foreground hover:opacity-90 cursor-pointer">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModal(t) {
    document.getElementById('modal').style.display = 'block';
    var form = document.getElementById('modal-form');
    var esEdit = !!t;
    document.getElementById('modal-titulo').textContent = esEdit ? 'Editar tipo de descuento' : 'Nuevo tipo de descuento';
    document.getElementById('campo-method').value = esEdit ? 'PUT' : 'POST';
    form.action = esEdit ? '{{ route("discount-types.update", "ID") }}'.replace('ID', t.id) : '{{ route("discount-types.store") }}';
    document.getElementById('campo-codigo').value = esEdit ? t.codigo : '';
    document.getElementById('campo-nombre').value = esEdit ? t.nombre : '';
    document.getElementById('campo-tipo').value = esEdit ? t.tipo_aplicacion : 'PORCENTAJE';
    document.getElementById('campo-porcentaje').value = esEdit && t.porcentaje !== null ? t.porcentaje : '';
    document.getElementById('campo-importe').value = esEdit && t.importe_fijo !== null ? t.importe_fijo : '';
    document.getElementById('campo-orden').value = esEdit ? t.orden : 1;
    document.getElementById('campo-estado').value = esEdit ? t.estado : 'ACTIVO';
    document.getElementById('campo-codigo').focus();
}
function cerrarModal() { document.getElementById('modal').style.display = 'none'; }
</script>
@endsection