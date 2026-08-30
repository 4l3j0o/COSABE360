@push('scripts')
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
            var relacion = (pg && pg.pivot) ? pg.pivot.tipo_relacion : (pg && pg.relacion ? pg.relacion : 'PADRE');
            var esTutor = (pg && pg.pivot) ? (pg.pivot.es_principal ? 1 : 0) : (pg && (pg.es_tutor === '1' || pg.es_tutor === 1) ? 1 : 0);
            var idFamiliar = (p.id || p.id_existente || '');
            var opciones = '';
            var rels = ['PADRE','MADRE','TIO','TIA','ABUELO','ABUELA','HERMANO','HERMANA','OTRO','TUTOR'];
            for (var r = 0; r < rels.length; r++) {
                opciones += '<option value="' + rels[r] + '"' + (relacion === rels[r] ? ' selected' : '') + '>' + ETIQUETAS_RELACION[rels[r]] + '</option>';
            }
            var vinculadoHtml = '';
            if (idFamiliar && (p.nombres || p.apellidos)) {
                vinculadoHtml = 'Vinculado a: <strong>' + (p.nombres||'') + ' ' + (p.apellidos||'') + '</strong>' + ((p.numero_documento) ? ' — ' + p.numero_documento : '');
            }
            return '<div class="fila-familiar" data-idx="' + idx + '" style="border:1px solid var(--border); border-radius:0.75rem; padding:1rem; margin-bottom:1rem; background:var(--card);">' +
                  '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">' +
                      '<h4 class="text-sm font-semibold text-foreground">Familiar</h4>' +
                      '<button type="button" onclick="quitarFilaFamiliar(this)" class="text-xs text-error hover:underline cursor-pointer">Quitar</button>' +
                  '</div>' +
                  '<div style="position:relative; margin-bottom:0.75rem;">' +
                      '<input type="text" class="buscador-familiar" placeholder="Buscar familiar / tutor ya registrado (CI o nombre)..." autocomplete="off" onfocus="abrirBusquedaFamiliar(this)" oninput="buscarPersonaFamiliar(this)" value="" style="width:100%; padding:0.5rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;">' +
                      '<div class="resultados-familiar" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:60; max-height:220px; overflow-y:auto; background:var(--card); border:1px solid var(--border); border-radius:0.5rem; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15);"></div>' +
                  '</div>' +
                  '<div class="familiar-seleccionado" style="' + (vinculadoHtml ? 'display:flex;' : 'display:none;') + ' align-items:center; justify-content:space-between; gap:0.5rem; margin-bottom:0.75rem; padding:0.5rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--muted);">' +
                      '<span class="fs-info" style="font-size:0.8125rem; color:var(--foreground);">' + vinculadoHtml + '</span>' +
                      '<button type="button" onclick="quitarVinculacionFamiliar(this)" style="font-size:0.75rem; color:var(--error); cursor:pointer; background:none; border:none; text-decoration:underline;">Cambiar</button>' +
                  '</div>' +
                  '<input type="hidden" data-field="id_existente" name="familiares[' + idx + '][id_existente]" class="f-id" value="' + idFamiliar + '">' +
                  '<input type="hidden" data-field="tipo_doc" name="familiares[' + idx + '][tipo_doc]" class="f-tipo_doc" value="' + (p.tipo_documento || p.tipo_doc || 'CI') + '">' +
                  '<input type="hidden" data-field="complemento" name="familiares[' + idx + '][complemento]" class="f-complemento" value="' + (p.complemento || '') + '">' +
                  '<input type="hidden" data-field="direccion" name="familiares[' + idx + '][direccion]" class="f-direccion" value="' + (p.direccion || '') + '">' +
                  '<input type="hidden" data-field="ocupacion" name="familiares[' + idx + '][ocupacion]" class="f-ocupacion" value="' + (p.ocupacion || '') + '">' +
                  '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombres</label><input type="text" data-field="nombres" name="familiares[' + idx + '][nombres]" class="f-nombres" value="' + (p.nombres || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Apellidos</label><input type="text" data-field="apellidos" name="familiares[' + idx + '][apellidos]" class="f-apellidos" value="' + (p.apellidos || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
                      '<div><label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">N° Documento</label><input type="text" data-field="num_doc" name="familiares[' + idx + '][num_doc]" class="f-num_doc" value="' + (p.numero_documento || p.num_doc || '') + '" style="width:100%; padding:0.625rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--background); color:var(--foreground); font-size:0.875rem;"></div>' +
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

        var REGISTRO_TUTORES = null;

        function _claveTutor(p) {
            return ((((p.nombres || '') + ' ' + (p.apellidos || '') + ' ' + (p.numero_documento || '') + ' ' + (p.complemento || ''))).toLowerCase());
        }

        function abrirBusquedaFamiliar(input) {
            if (REGISTRO_TUTORES) { renderResultadosTutor(input.closest('.fila-familiar')); return; }
            fetch('{{ route("students.buscarPersona") }}')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    REGISTRO_TUTORES = data || [];
                    renderResultadosTutor(input.closest('.fila-familiar'));
                });
        }

        function buscarPersonaFamiliar(input) {
            renderResultadosTutor(input.closest('.fila-familiar'));
        }

        function renderResultadosTutor(fila) {
            var dd = fila.querySelector('.resultados-familiar');
            var input = fila.querySelector('.buscador-familiar');
            if (!dd || !input || !REGISTRO_TUTORES) return;
            var q = (input.value || '').trim().toLowerCase();
            var idsUsados = {};
            var cont = fila.parentNode;
            if (cont) {
                cont.querySelectorAll('.fila-familiar').forEach(function (f) {
                    var id = f.querySelector('.f-id').value;
                    if (id) idsUsados[id] = true;
                });
            }
            var items = [];
            for (var i = 0; i < REGISTRO_TUTORES.length; i++) {
                var p = REGISTRO_TUTORES[i];
                if (idsUsados[p.id]) continue;
                if (!p.numero_documento && !p.nombres) continue;
                if (q && _claveTutor(p).indexOf(q) === -1) continue;
                items.push(p);
            }
            if (!items.length) {
                dd.innerHTML = '<div style="padding:0.625rem 0.75rem; font-size:0.875rem; color:var(--muted-foreground);">' +
                    (q ? 'Sin coincidencias. Complete los datos de abajo para registrar esta persona como familiar/tutor.' : 'No hay familiares/tutores registrados todavía.') +
                    '</div>';
            } else {
                var html = '';
                for (var j = 0; j < items.length; j++) {
                    var it = items[j];
                    html += '<div class="px-3 py-2 cursor-pointer text-sm border-b border-border last:border-0 hover:bg-muted transition-colors" onmousedown="seleccionarPersonaFamiliar(this); return false;" data-index="' + j + '" style="cursor:pointer;">' +
                        '<div class="font-semibold text-foreground">' + (it.nombres || '') + ' ' + (it.apellidos || '') + '</div>' +
                        '<div class="text-xs text-muted-foreground">' + (it.numero_documento || '') + (it.complemento ? ' (comp. ' + it.complemento + ')' : '') + (it.telefono ? ' • ' + it.telefono : '') + '</div>' +
                    '</div>';
                }
                dd.innerHTML = html;
            }
            dd._data = items;
            dd.style.display = 'block';
        }

        function seleccionarPersonaFamiliar(el) {
            var fila = el.closest('.fila-familiar');
            var dd = fila.querySelector('.resultados-familiar');
            var p = (dd._data || [])[el.getAttribute('data-index')] || {};
            if (!p.id) return;
            fila.querySelector('.f-id').value = p.id;
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
            var info = fila.querySelector('.fs-info');
            if (info) info.innerHTML = 'Vinculado a: <strong>' + (p.nombres || '') + ' ' + (p.apellidos || '') + '</strong> — ' + (p.numero_documento || '');
            var resumen = fila.querySelector('.familiar-seleccionado');
            if (resumen) resumen.style.display = 'flex';
            fila.querySelector('.buscador-familiar').value = '';
            dd.style.display = 'none';
            dd._data = [];
        }

        function quitarVinculacionFamiliar(btn) {
            var fila = btn.closest('.fila-familiar');
            fila.querySelector('.f-id').value = '';
            var resumen = fila.querySelector('.familiar-seleccionado');
            if (resumen) resumen.style.display = 'none';
            var input = fila.querySelector('.buscador-familiar');
            if (input) input.focus();
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

        document.addEventListener('click', function (e) {
            if (!(e.target && e.target.closest && e.target.closest('.fila-familiar'))) {
                document.querySelectorAll('.resultados-familiar').forEach(function (dd) { dd.style.display = 'none'; });
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.resultados-familiar').forEach(function (dd) { dd.style.display = 'none'; });
            }
        });
    </script>
@endpush