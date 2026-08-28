@extends('layouts.app')

@section('titulo', 'Configuración Tributaria')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')

    @if(session('mensaje'))
        <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">
            {{ session('mensaje') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
        </div>
        <button type="button" id="btn-editar"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-all cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </button>
    </div>

    <form method="POST" action="{{ route('tax-configuration.update') }}" id="form-config">
        @csrf
        @method('PUT')

        <div class="space-y-4 md:space-y-5">

            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 bg-primary/10 border-b border-primary/20">
                    <h3 class="text-sm font-semibold text-primary">Datos de la Entidad</h3>
                </div>
                <div class="p-4 md:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">NIT</p>
                        <p class="read-view text-sm text-foreground font-medium">{{ $config->nit_colegio ?: '—' }}</p>
                        <input type="text" name="nit_colegio" value="{{ old('nit_colegio', $config->nit_colegio) }}" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('nit_colegio') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Razón Social</p>
                        <p class="read-view text-sm text-foreground font-medium">{{ $config->razon_social ?: '—' }}</p>
                        <input type="text" name="razon_social" value="{{ old('razon_social', $config->razon_social) }}" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('razon_social') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tipo Entidad</p>
                        <p class="read-view text-sm text-foreground font-medium">{{ $config->tipo_entidad === 'CON_FINES_LUCRO' ? 'Con fines de lucro' : 'Sin fines de lucro' }}</p>
                        <select name="tipo_entidad" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                            <option value="CON_FINES_LUCRO" {{ old('tipo_entidad', $config->tipo_entidad) === 'CON_FINES_LUCRO' ? 'selected' : '' }}>Con fines de lucro</option>
                            <option value="SIN_FINES_LUCRO" {{ old('tipo_entidad', $config->tipo_entidad) === 'SIN_FINES_LUCRO' ? 'selected' : '' }}>Sin fines de lucro</option>
                        </select>
                        @error('tipo_entidad') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Estado</p>
                        <p class="read-view text-sm font-medium">
                            @if($config->estado === 'ACTIVO')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-success/10 text-success text-xs font-medium">● Activo</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-error/10 text-error text-xs font-medium">● Inactivo</span>
                            @endif
                        </p>
                        <select name="estado" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                            <option value="ACTIVO" {{ old('estado', $config->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                            <option value="INACTIVO" {{ old('estado', $config->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Monto mensualidad (Bs)</p>
                        <p class="read-view text-sm text-foreground font-medium">{{ $config->monto_mensualidad != null ? 'Bs ' . number_format((float) $config->monto_mensualidad, 2) : '—' }}</p>
                        <input type="number" name="monto_mensualidad" value="{{ old('monto_mensualidad', $config->monto_mensualidad ?? 0) }}" step="0.01" min="0" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('monto_mensualidad') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 bg-primary/10 border-b border-primary/20">
                    <h3 class="text-sm font-semibold text-primary">Servicios de Facturación Electrónica</h3>
                </div>
                <div class="p-4 md:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Modalidad SFE</p>
                        <p class="read-view text-sm text-foreground font-medium">
                            @switch($config->modalidad_sfe)
                                @case('COMPUTARIZADA_EN_LINEA') Computarizada en línea @break
                                @case('ELECTRONICA_EN_LINEA') Electrónica en línea @break
                                @case('PORTAL_WEB') Portal web @break
                            @endswitch
                        </p>
                        <select name="modalidad_sfe" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                            <option value="COMPUTARIZADA_EN_LINEA" {{ old('modalidad_sfe', $config->modalidad_sfe) === 'COMPUTARIZADA_EN_LINEA' ? 'selected' : '' }}>Computarizada en línea</option>
                            <option value="ELECTRONICA_EN_LINEA" {{ old('modalidad_sfe', $config->modalidad_sfe) === 'ELECTRONICA_EN_LINEA' ? 'selected' : '' }}>Electrónica en línea</option>
                            <option value="PORTAL_WEB" {{ old('modalidad_sfe', $config->modalidad_sfe) === 'PORTAL_WEB' ? 'selected' : '' }}>Portal web</option>
                        </select>
                        @error('modalidad_sfe') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">CUIS</p>
                        <p class="read-view text-sm text-foreground font-medium">{{ $config->cuis ?: '—' }}</p>
                        <input type="text" name="cuis" value="{{ old('cuis', $config->cuis) }}"
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('cuis') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Código Sucursal</p>
                        <p class="read-view text-sm text-foreground font-medium">{{ $config->codigo_sucursal }}</p>
                        <input type="text" name="codigo_sucursal" value="{{ old('codigo_sucursal', $config->codigo_sucursal) }}" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('codigo_sucursal') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 bg-primary/10 border-b border-primary/20">
                    <h3 class="text-sm font-semibold text-primary">Exenciones Tributarias</h3>
                </div>
                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/30">
                            <span class="read-view text-lg shrink-0">{{ $config->exento_iva_educacion ? '✅' : '—' }}</span>
                            <input type="checkbox" name="exento_iva_educacion" value="1" {{ old('exento_iva_educacion', $config->exento_iva_educacion) ? 'checked' : '' }}
                                class="edit-view w-4 h-4 rounded border-input text-primary focus:ring-primary/30 shrink-0 hidden">
                            <span class="text-sm text-foreground">Exento IVA educación</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/30">
                            <span class="read-view text-lg shrink-0">{{ $config->exento_it ? '✅' : '—' }}</span>
                            <input type="checkbox" name="exento_it" value="1" {{ old('exento_it', $config->exento_it) ? 'checked' : '' }}
                                class="edit-view w-4 h-4 rounded border-input text-primary focus:ring-primary/30 shrink-0 hidden">
                            <span class="text-sm text-foreground">Exento IT</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/30">
                            <span class="read-view text-lg shrink-0">{{ $config->iue_exento ? '✅' : '—' }}</span>
                            <input type="checkbox" name="iue_exento" value="1" {{ old('iue_exento', $config->iue_exento) ? 'checked' : '' }}
                                class="edit-view w-4 h-4 rounded border-input text-primary focus:ring-primary/30 shrink-0 hidden">
                            <span class="text-sm text-foreground">IUE exento</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/30">
                            <span class="read-view text-lg shrink-0">{{ $config->agente_retencion_rc_iva ? '✅' : '—' }}</span>
                            <input type="checkbox" name="agente_retencion_rc_iva" value="1" {{ old('agente_retencion_rc_iva', $config->agente_retencion_rc_iva) ? 'checked' : '' }}
                                class="edit-view w-4 h-4 rounded border-input text-primary focus:ring-primary/30 shrink-0 hidden">
                            <span class="text-sm text-foreground">Agente retención RC-IVA</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 bg-primary/10 border-b border-primary/20">
                    <h3 class="text-sm font-semibold text-primary">Leyendas Obligatorias en Facturas</h3>
                </div>
                <div class="p-4 md:p-6 space-y-4">
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Leyenda IVA Exento</p>
                        <p class="read-view text-sm text-foreground">{{ $config->leyenda_iva_exento ?: '—' }}</p>
                        <input type="text" name="leyenda_iva_exento" value="{{ old('leyenda_iva_exento', $config->leyenda_iva_exento) }}" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('leyenda_iva_exento') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Leyenda IT Exento</p>
                        <p class="read-view text-sm text-foreground">{{ $config->leyenda_it_exento ?: '—' }}</p>
                        <input type="text" name="leyenda_it_exento" value="{{ old('leyenda_it_exento', $config->leyenda_it_exento) }}" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('leyenda_it_exento') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Leyenda Ley 070</p>
                        <p class="read-view text-sm text-foreground">{{ $config->leyenda_ley070 ?: '—' }}</p>
                        <input type="text" name="leyenda_ley070" value="{{ old('leyenda_ley070', $config->leyenda_ley070) }}" required
                            class="edit-view w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all hidden">
                        @error('leyenda_ley070') <p class="text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </div>

        <div id="barra-acciones" class="hidden mt-5 md:mt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
            <button type="button" id="btn-cancelar"
                class="text-sm font-medium text-muted-foreground hover:text-foreground px-4 py-2.5 rounded-lg border border-border hover:bg-accent transition-all cursor-pointer text-center">
                Cancelar
            </button>
            <button type="submit"
                class="bg-primary text-primary-foreground text-sm font-semibold py-2.5 px-5 rounded-lg hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer text-center">
                Guardar cambios
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        const btnEditar = document.getElementById('btn-editar');
        const btnCancelar = document.getElementById('btn-cancelar');
        const barraAcciones = document.getElementById('barra-acciones');
        const readViews = document.querySelectorAll('.read-view');
        const editViews = document.querySelectorAll('.edit-view');

        function activarEdicion() {
            readViews.forEach(el => el.classList.add('hidden'));
            editViews.forEach(el => el.classList.remove('hidden'));
            btnEditar.classList.add('hidden');
            barraAcciones.classList.remove('hidden');
        }

        function cancelarEdicion() {
            readViews.forEach(el => el.classList.remove('hidden'));
            editViews.forEach(el => el.classList.add('hidden'));
            btnEditar.classList.remove('hidden');
            barraAcciones.classList.add('hidden');
        }

        btnEditar.addEventListener('click', activarEdicion);
        btnCancelar.addEventListener('click', cancelarEdicion);
    </script>
    @endpush
@endsection
