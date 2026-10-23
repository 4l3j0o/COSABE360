@php
    $edit = $editAnnouncement ?? null;
    $rec = $edit?->recipients ?? collect();
    $tipoActual = $rec->first()?->tipo_destinatario;
    $sectionIdsActual = $rec->whereNotNull('section_id')->pluck('section_id')->map(fn ($id) => (int) $id)->all();
    $levelIdActual = $rec->first()?->level_id;
    $mostrarSecciones = in_array($tipoActual, ['CURSO_ESPECIFICO', 'DOCENTES_CURSO'], true);
@endphp

{{-- MODAL EDITAR --}}
<div id="modal-edit" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
    <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
        <div style="width:100%; max-width:32rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Comunicado</h2>
                <button type="button" onclick="closeModal('modal-edit')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                    <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="form-edit" method="POST" enctype="multipart/form-data" @if($edit) action="{{ route('announcements.update', $edit) }}" @endif style="padding:1.5rem;">
                @csrf @method('PUT')
                <input type="hidden" name="_f[tab]" value="enviados">
                <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos del Comunicado</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Título <span class="text-error">*</span></label>
                            <input type="text" name="titulo" id="edit-titulo" required maxlength="200" value="{{ $edit?->titulo }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Contenido <span class="text-error">*</span></label>
                            <textarea name="contenido" id="edit-contenido" required rows="4" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ $edit?->contenido }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Destinatario <span class="text-error">*</span></label>
                            <select name="tipo_destinatario" id="edit-tipo" required onchange="toggleSectionSelect('edit')" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @foreach($tipos as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ $tipoActual === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="edit-section-wrap" style="display:{{ $mostrarSecciones ? 'block' : 'none' }};">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Paralelos</label>
                            <select name="section_ids[]" id="edit-section_ids" multiple size="6" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ in_array($section->id, $sectionIdsActual, true) ? 'selected' : '' }}>{{ $section->codigoCompleto() }} · {{ $section->grade->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Puede seleccionar varios (Ctrl/Cmd + clic).</p>
                        </div>
                        <div id="edit-level-wrap" style="display:{{ $tipoActual === 'NIVEL_DOCENTES' ? 'block' : 'none' }};">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nivel</label>
                            <select name="level_id" id="edit-level_id" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">Seleccionar nivel...</option>
                                @foreach($niveles as $nivel)
                                    <option value="{{ $nivel->id }}" {{ (int) $levelIdActual === (int) $nivel->id ? 'selected' : '' }}>{{ $nivel->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha publicación</label>
                            <input type="datetime-local" name="fecha_publicacion" id="edit-fecha" value="{{ $edit?->fecha_publicacion?->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado</label>
                            <select name="estado" id="edit-estado" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="BORRADOR" {{ $edit?->estado === 'BORRADOR' ? 'selected' : '' }}>Borrador</option>
                                <option value="PUBLICADO" {{ $edit?->estado === 'PUBLICADO' ? 'selected' : '' }}>Publicado</option>
                                <option value="ARCHIVADO" {{ $edit?->estado === 'ARCHIVADO' ? 'selected' : '' }}>Archivado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Imagen <span class="normal-case font-normal text-muted-foreground">(reemplaza la actual, hasta 2MB)</span></label>
                            <div id="edit-imagen-actual" class="mb-2">
                                @if($edit?->imagen_path)
                                    <img src="{{ \App\Support\ArchivoPrivado::url($edit->imagen_path) }}" alt="" style="max-height:6rem;border-radius:0.5rem;border:1px solid var(--border);">
                                @endif
                            </div>
                            <input type="file" name="imagen" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="inline-flex items-center gap-2 text-sm text-foreground cursor-pointer">
                                <input type="checkbox" name="destacado" id="edit-destacado" value="1" {{ $edit?->destacado ? 'checked' : '' }} class="accent-primary">
                                <span class="font-medium">Destacado <span class="text-muted-foreground font-normal">— pantallazo al ingresar</span></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border);">
                    <button type="button" onclick="closeModal('modal-edit')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                    <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
