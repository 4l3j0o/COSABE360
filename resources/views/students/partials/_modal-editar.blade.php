@push('modals')
    {{-- MODAL EDITAR (compartido entre listado y ficha del estudiante) --}}
    <div id="modal-edit" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
        <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
            <div style="width:100%; max-width:48rem; max-height:calc(100vh - 4rem); display:flex; flex-direction:column; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="display:flex; flex-shrink:0; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Editar Estudiante</h2>
                    <button onclick="closeModal('modal-edit')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="form-edit" method="POST" enctype="multipart/form-data" style="flex:1; overflow-y:auto; padding:1.5rem;">
                    @csrf @method('PUT')
                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres <span class="text-error">*</span></label>
                                <input type="text" name="nombres" id="e-nombres" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos <span class="text-error">*</span></label>
                                <input type="text" name="apellidos" id="e-apellidos" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fecha de Nacimiento <span class="text-error">*</span></label>
                                <input type="date" name="fecha_nacimiento" id="e-fecha_nacimiento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Lugar de Nacimiento</label>
                                <input type="text" name="lugar_nacimiento" id="e-lugar_nacimiento" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Sexo <span class="text-error">*</span></label>
                                <select name="sexo" id="e-sexo" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Estado <span class="text-error">*</span></label>
                                <select name="estado" id="e-estado" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                    <option value="EGRESADO">Egresado</option>
                                    <option value="TRASLADADO">Trasladado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Documento de Identidad</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo <span class="text-error">*</span></label>
                                <select name="tipo_documento" id="e-tipo_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="CI">Cédula de Identidad</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Número <span class="text-error">*</span></label>
                                <input type="text" name="numero_documento" id="e-numero_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" id="e-complemento" maxlength="5" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Contacto</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Dirección</label>
                                <input type="text" name="direccion" id="e-direccion" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" id="e-telefono" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del Estudiante</label>
                                <input type="email" name="email_estudiante" id="e-email_estudiante" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener el actual.</p>
                            </div>
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.25rem;">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Familia y Tutor <span class="text-error">*</span></h3>
                            <button type="button" id="btn-agregar-familiar-edit" onclick="agregarFilaFamiliarEdit()" class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-2 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar familiar
                            </button>
                        </div>
                        <p class="text-xs text-muted-foreground mb-4">Agregue a los familiares del estudiante. Para cada uno elija su <strong>relación</strong> y marque quién será el <strong>tutor</strong> (debe haber exactamente uno). Si no es de la familia, use la relación <strong>TUTOR</strong>.</p>

                        <div id="lista-familiares-edit" style="display:none;"></div>
                        <div id="sin-familiar-edit" class="text-xs text-muted-foreground bg-muted/40 border border-dashed border-border rounded-lg px-4 py-5 text-center">
                            No hay familiares registrados para este estudiante. Use <strong>"Agregar familiar"</strong>.
                        </div>
                        @error('familiares') <p class="text-xs text-error mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div style="background:rgba(0,0,0,0.03); border-radius:0.75rem; padding:1.25rem; border:1px solid rgba(0,0,0,0.05); margin-bottom:1.25rem;">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Información Adicional</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Idioma</label>
                                <input type="text" name="idioma" id="e-idioma" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Cultura</label>
                                <input type="text" name="cultura" id="e-cultura" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Discapacidad</label>
                                <input type="text" name="discapacidad" id="e-discapacidad" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Fotografía</label>
                                <input type="file" name="fotografia" accept="image/*" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <p class="text-xs text-muted-foreground mt-1">Dejar vacío para mantener la actual.</p>
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

    <script>
        var ETIQUETAS_RELACION = {
            PADRE: 'Padre',
            MADRE: 'Madre',
            TIO: 'Tío',
            TIA: 'Tía',
            ABUELO: 'Abuelo',
            ABUELA: 'Abuela',
            HERMANO: 'Hermano',
            HERMANA: 'Hermana',
            OTRO: 'Otro Familiar',
            TUTOR: 'Tutor (no familia)'
        };

        function construirFilaFamiliar(idx, pg, esEdit) {
            var p = pg || {};
            var relacion = (pg && pg.pivot) ? pg.pivot.tipo_relacion : 'PADRE';
            var esTutor = (pg && pg.pivot) ? (pg.pivot.es_principal ? 1 : 0) : 0;
            var sel = esEdit ? 'lista-familiares-edit' : 'lista-familiares';
            var opciones = '';
            var rels = ['PADRE','MADRE','TIO','TIA','ABUELO','ABUELA','HERMANO','HERMANA','OTRO','TUTOR'];
            for (var r = 0; r < rels.length; r++) {
                opciones += '<option value="' + rels[r] + '"' + (relacion === rels[r] ? ' selected' : '') + '>' + ETIQUETAS_RELACION[rels[r]] + '</option>';
            }
            return '<div class="fila-familiar" data-idx="' + idx + '" style="border:1px solid var(--border); border-radius:0.75rem; padding:1rem; margin-bottom:1rem; background:var(--card);">' +
                  '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">' +
                      '<h4 class="text-sm font-semibold text-foreground">Familiar</h4>' +
                      '<button type="button" onclick="quitarFilaFamiliar(this)" class="text-xs text-error hover:underline cursor-pointer">Quitar</button>' +
                  '</div>' +
                  '<div style="margin-bottom:0.75rem;">' +
                      '<input type="text" class="buscador-familiar" placeholder="Buscar persona existente por CI o nombre..." oninput="buscarPersonaFamiliar(this)" value="" style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' +
                      '<div class="resultados-familiar hidden"></div>' +
                  '</div>' +
                  '<input type="hidden" data-field="id_existente" name="familiares[' + idx + '][id_existente]" class="f-id" value="' + (p.id || '') + '">' +
                  '<input type="hidden" data-field="tipo_doc" name="familiares[' + idx + '][tipo_doc]" class="f-tipo_doc" value="' + (p.tipo_documento || 'CI') + '">' +
                  '<input type="hidden" data-field="complemento" name="familiares[' + idx + '][complemento]" class="f-complemento" value="' + (p.complemento || '') + '">' +
                  '<input type="hidden" data-field="direccion" name="familiares[' + idx + '][direccion]" class="f-direccion" value="' + (p.direccion || '') + '">' +
                  '<input type="hidden" data-field="ocupacion" name="familiares[' + idx + '][ocupacion]" class="f-ocupacion" value="' + (p.ocupacion || '') + '">' +
                  '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" data-field="nombres" name="familiares[' + idx + '][nombres]" class="f-nombres" value="' + (p.nombres || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" data-field="apellidos" name="familiares[' + idx + '][apellidos]" class="f-apellidos" value="' + (p.apellidos || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" data-field="num_doc" name="familiares[' + idx + '][num_doc]" class="f-num_doc" value="' + (p.numero_documento || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Teléfono</label><input type="text" data-field="telefono" name="familiares[' + idx + '][telefono]" class="f-telefono" value="' + (p.telefono || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Relación</label><select data-field="relacion" name="familiares[' + idx + '][relacion]" class="f-relacion" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' + opciones + '</select></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">¿Es tutor?</label><label class="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" data-field="es_tutor" name="familiares[' + idx + '][es_tutor]" class="f-es_tutor" value="1" ' + (esTutor ? 'checked' : '') + ' onchange="marcarTutor(this)"> Es el tutor</label></div>' +
                  '</div>' +
                  '<div class="campo-email-tutor" style="' + (esTutor ? '' : 'display:none;') + 'margin-top:0.75rem;">' +
                      '<label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Correo del tutor (para acceso al sistema) <span class="text-error">*</span></label>' +
                      '<input type="email" data-field="email" name="familiares[' + idx + '][email]" class="f-email" value="' + (p.email || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' +
                      '<p class="text-xs text-muted-foreground mt-1">El tutor es quien recibe el acceso al sistema.</p>' +
                  '</div>' +
              '</div>';
        }

        var familiarSeqEdit = 0;
        function agregarFilaFamiliarEdit(editIdx) {
            var cont = document.getElementById('lista-familiares-edit');
            cont.insertAdjacentHTML('beforeend', construirFilaFamiliar(editIdx, null, true));
            familiarSeqEdit++;
            cont.style.display = 'block';
            var sn = document.getElementById('sin-familiar-edit');
            if (sn) sn.style.display = 'none';
            renumerarFilas('lista-familiares-edit');
        }

        function quitarFilaFamiliar(btn) {
            var fila = btn.closest('.fila-familiar');
            var cont = fila.parentNode;
            fila.remove();
            renumerarFilas(cont.id);
        }

        function renumerarFilas(contenedorId) {
            var cont = document.getElementById(contenedorId);
            if (!cont) return;
            var filas = cont.querySelectorAll('.fila-familiar');
            var idx = 0;
            filas.forEach(function(fila) {
                fila.dataset.idx = idx;
                fila.querySelectorAll('[data-field]').forEach(function(input) {
                    var campo = input.dataset.field;
                    input.name = 'familiares[' + idx + '][' + campo + ']';
                });
                var buscador = fila.querySelector('.buscador-familiar');
                if (buscador) buscador.oninput = (function(f){ return function(){ buscarPersonaFamiliar(f); }; })(buscador);
                idx++;
            });
            var vacio = (contenedorId === 'lista-familiares') ? 'sin-familiar' : 'sin-familiar-edit';
            if (idx === 0) {
                cont.style.display = 'none';
                var v = document.getElementById(vacio);
                if (v) v.style.display = 'block';
            }
        }

        var busquedaTimeout = null;
        function buscarPersonaFamiliar(input) {
            var fila = input.closest('.fila-familiar');
            var dd = fila.querySelector('.resultados-familiar');
            var query = input.value;
            clearTimeout(busquedaTimeout);
            if (query.length < 2) { dd.classList.add('hidden'); dd.innerHTML = ''; return; }
            busquedaTimeout = setTimeout(function() {
                fetch('{{ route("students.buscarPersona") }}?q=' + encodeURIComponent(query))
                .then(r => r.json()).then(data => {
                    if (!data.length) { dd.classList.add('hidden'); return; }
                    dd.innerHTML = data.map(function(p) {
                        return '<div class="px-3 py-2 cursor-pointer hover:bg-muted text-sm border-b border-border last:border-0" onclick="seleccionarPersonaFamiliar(this)">' +
                            '<span class="font-semibold">' + (p.nombres||'') + ' ' + (p.apellidos||'') + '</span> — ' + (p.numero_documento||'') +
                            '</div>';
                    }).join('');
                    dd.classList.remove('hidden');
                    dd._data = data;
                });
            }, 300);
        }

        function seleccionarPersonaFamiliar(el) {
            var fila = el.closest('.fila-familiar');
            var dd = fila.querySelector('.resultados-familiar');
            var data = dd._data || [];
            var li = Array.prototype.indexOf.call(dd.children, el);
            var p = data[li] || {};
            fila.querySelector('.f-id').value = p.id || '';
            fila.querySelector('.f-nombres').value = p.nombres || '';
            fila.querySelector('.f-apellidos').value = p.apellidos || '';
            fila.querySelector('.f-num_doc').value = p.numero_documento || '';
            fila.querySelector('.f-tipo_doc').value = p.tipo_documento || 'CI';
            fila.querySelector('.f-complemento').value = p.complemento || '';
            fila.querySelector('.f-telefono').value = p.telefono || '';
            fila.querySelector('.f-email').value = p.email || '';
            fila.querySelector('.f-direccion').value = p.direccion || '';
            fila.querySelector('.f-ocupacion').value = p.ocupacion || '';
            fila.querySelector('.f-es_tutor').checked = true;
            var campoCorreo = fila.querySelector('.campo-email-tutor');
            if (campoCorreo) campoCorreo.style.display = 'block';
            marcarTutor(fila.querySelector('.f-es_tutor'));
            fila.querySelector('.buscador-familiar').value = '';
            dd.classList.add('hidden');
            dd.innerHTML = '';
        }

        function marcarTutor(cb) {
            var fila = cb.closest('.fila-familiar');
            var cont = fila.parentNode;
            var hermanos = cont.querySelectorAll('.f-es_tutor');
            hermanos.forEach(function(h) { if (h !== cb) h.checked = false; });
            cont.querySelectorAll('.campo-email-tutor').forEach(function(c) { c.style.display = 'none'; });
            if (cb.checked) {
                var cc = fila.querySelector('.campo-email-tutor');
                if (cc) cc.style.display = 'block';
            }
        }

        function editarEstudiante(btn) {
            var s = estudiantesMap[btn.dataset.id];
            if (!s) return;
            document.getElementById('form-edit').action = '{{ url("students") }}/' + s.id;
            document.getElementById('e-nombres').value = s.nombres || '';
            document.getElementById('e-apellidos').value = s.apellidos || '';
            document.getElementById('e-fecha_nacimiento').value = s.fecha_nacimiento ? s.fecha_nacimiento.substring(0, 10) : '';
            document.getElementById('e-lugar_nacimiento').value = s.lugar_nacimiento || '';
            document.getElementById('e-sexo').value = s.sexo || '';
            document.getElementById('e-estado').value = s.estado || 'ACTIVO';
            document.getElementById('e-tipo_documento').value = s.tipo_documento || 'CI';
            document.getElementById('e-numero_documento').value = s.numero_documento || '';
            document.getElementById('e-complemento').value = s.complemento || '';
            document.getElementById('e-direccion').value = s.direccion || '';
            document.getElementById('e-telefono').value = s.telefono || '';
            document.getElementById('e-email_estudiante').value = s.email || '';
            document.getElementById('e-idioma').value = s.idioma || '';
            document.getElementById('e-cultura').value = s.cultura || '';
            document.getElementById('e-discapacidad').value = s.discapacidad || '';

            var cont = document.getElementById('lista-familiares-edit');
            cont.innerHTML = '';
            familiarSeqEdit = 0;
            var pgs = s.parent_guardians || [];
            pgs.forEach(function(p) {
                var idx = familiarSeqEdit;
                cont.insertAdjacentHTML('beforeend', construirFilaFamiliar(idx, p, true));
                familiarSeqEdit++;
            });
            if (pgs.length > 0) {
                cont.style.display = 'block';
                var sn = document.getElementById('sin-familiar-edit');
                if (sn) sn.style.display = 'none';
            } else {
                cont.style.display = 'none';
                var sn2 = document.getElementById('sin-familiar-edit');
                if (sn2) sn2.style.display = 'block';
            }
            renumerarFilas('lista-familiares-edit');
            openModal('modal-edit');
        }
    </script>
@endpush