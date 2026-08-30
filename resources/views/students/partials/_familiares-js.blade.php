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
    </script>
@endpush