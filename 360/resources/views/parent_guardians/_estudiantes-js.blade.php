<script>
    const ESTUDIANTES = @json($estudiantes);
    const ESTUDIANTES_CON_TUTOR = @json(array_map('strval', $estudiantesConTutor ?? []));
    const CON_TUTOR_SET = new Set(ESTUDIANTES_CON_TUTOR);

    function openModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function opcionesTipo(selected, modo) {
        const tipos = ['PADRE', 'MADRE', 'TUTOR'];
        let opts = '<option value=""' + (selected ? '' : ' selected') + '>— Tipo —</option>';
        tipos.forEach(function (t) {
            const sel = (selected === t) ? ' selected' : '';
            opts += '<option value="' + t + '"' + sel + '>' + t.charAt(0) + t.slice(1).toLowerCase() + '</option>';
        });
        return opts;
    }

    function _claveEstudiante(e) {
        return ((((e.nombres || '') + ' ' + (e.apellidos || '') + ' ' + (e.codigo || '') + ' ' + (e.numero_documento || ''))).toLowerCase());
    }

    function estudiantePorId(id) {
        for (var i = 0; i < ESTUDIANTES.length; i++) {
            if (String(ESTUDIANTES[i].id) === String(id)) return ESTUDIANTES[i];
        }
        return null;
    }

    function abrirBusquedaEstudiante(input) {
        renderResultadosEstudiantes(input.closest('.fila-estudiante'));
    }

    function buscarEstudiante(input) {
        renderResultadosEstudiantes(input.closest('.fila-estudiante'));
    }

    function renderResultadosEstudiantes(fila) {
        var dd = fila.querySelector('.resultados-estudiantes');
        var input = fila.querySelector('.buscador-estudiante');
        if (!dd || !input) return;
        var q = (input.value || '').trim().toLowerCase();
        var idsUsados = {};
        var cont = fila.parentNode;
        if (cont) {
            cont.querySelectorAll('.fila-estudiante').forEach(function (f) {
                var v = f.querySelector('.f-est-id').value;
                if (v) idsUsados[v] = true;
            });
        }
        var items = [];
        for (var i = 0; i < ESTUDIANTES.length; i++) {
            var e = ESTUDIANTES[i];
            if (idsUsados[e.id]) continue;
            if (q && _claveEstudiante(e).indexOf(q) === -1) continue;
            items.push(e);
        }
        if (!items.length) {
            dd.innerHTML = '<div style="padding:0.625rem 0.75rem; font-size:0.875rem; color:var(--muted-foreground);">' +
                (q ? 'Sin coincidencias con estudiantes activos.' : 'No hay estudiantes activos registrados todavía.') +
                '</div>';
            dd.style.height = '';
            dd._data = items;
            dd.style.display = 'block';
            return;
        } else {
            // Mostramos los primeros 3 resultados; el resto se revela al hacer scroll.
            var EST_TOPE = 3;
            var html = '';
            var visible = Math.min(EST_TOPE, items.length);
            for (var j = 0; j < visible; j++) {
                var it = items[j];
                var yaTutor = CON_TUTOR_SET.has(String(it.id));
                html += '<div class="px-3 py-2 cursor-pointer text-sm border-b border-border last:border-0 hover:bg-muted transition-colors" onmousedown="seleccionarEstudiante(this); return false;" data-index="' + j + '">' +
                    '<div class="font-semibold text-foreground">' + (it.apellidos || '') + ' ' + (it.nombres || '') + (yaTutor ? ' <span style="color:var(--warning); font-size:0.6875rem; font-weight:600;">• con tutor</span>' : '') + '</div>' +
                    '<div class="text-xs text-muted-foreground">' + (it.codigo || '') + '</div>' +
                '</div>';
            }
            if (items.length > EST_TOPE) {
                // Deja espacio para que el scroll sea visible y se pueda bajar para ver el resto.
                html += '<div class="scroll-more" style="display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:' + (3 * 50) + 'px; color:var(--muted-foreground); font-size:0.8125rem; text-align:center;">▼ Desplácese para ver más (' + (items.length - EST_TOPE) + ' más)</div>';
            }
            dd.innerHTML = html;
            // Fijamos una altura de 220px cuando hay más de 3 para garantizar el scroll.
            if (items.length > EST_TOPE) {
                dd.style.height = '220px';
            } else {
                dd.style.height = '';
            }
            dd._data = items;
            dd._topeEst = visible;
            if (!dd._scrollEst) {
                dd._scrollEst = true;
                dd.addEventListener('scroll', function () {
                    if (dd.scrollTop + dd.clientHeight >= dd.scrollHeight - 10 && dd._topeEst < dd._data.length) {
                        var nuevoTope = Math.min(dd._topeEst + 10, dd._data.length);
                        var extraHtml = '';
                        for (var x = dd._topeEst; x < nuevoTope; x++) {
                            var ix = dd._data[x];
                            var yaT2 = CON_TUTOR_SET.has(String(ix.id));
                            extraHtml += '<div class="px-3 py-2 cursor-pointer text-sm border-b border-border last:border-0 hover:bg-muted transition-colors" onmousedown="seleccionarEstudiante(this); return false;" data-index="' + x + '">' +
                                '<div class="font-semibold text-foreground">' + (ix.apellidos || '') + ' ' + (ix.nombres || '') + (yaT2 ? ' <span style="color:var(--warning); font-size:0.6875rem; font-weight:600;">• con tutor</span>' : '') + '</div>' +
                                '<div class="text-xs text-muted-foreground">' + (ix.codigo || '') + '</div>' +
                            '</div>';
                        }
                        var sc = dd.querySelector('.scroll-more');
                        if (sc) sc.remove();
                        dd.insertAdjacentHTML('beforeend', extraHtml);
                        dd._topeEst = nuevoTope;
                        if (dd._topeEst < dd._data.length) {
                            dd.insertAdjacentHTML('beforeend', '<div class="scroll-more" style="padding:0.625rem 0.75rem; color:var(--muted-foreground); font-size:0.8125rem; text-align:center;">▼ Desplácese para ver más (' + (dd._data.length - dd._topeEst) + ' más)</div>');
                        }
                    }
                });
            }
        }
        dd.style.display = 'block';
    }

    function seleccionarEstudiante(el) {
        var fila = el.closest('.fila-estudiante');
        var dd = fila.querySelector('.resultados-estudiantes');
        var e = (dd._data || [])[el.getAttribute('data-index')] || {};
        if (!e.id) return;
        fila.querySelector('.f-est-id').value = e.id;
        fila._estYaTutor = CON_TUTOR_SET.has(String(e.id));
        var info = fila.querySelector('.es-info');
        if (info) info.innerHTML = '<strong>' + (e.apellidos || '') + ' ' + (e.nombres || '') + '</strong>' + (e.codigo ? ' — ' + e.codigo : '');
        var resumen = fila.querySelector('.estudiante-seleccionado');
        if (resumen) resumen.style.display = 'flex';
        fila.querySelector('.buscador-estudiante').value = '';
        dd.style.display = 'none';
        dd._data = [];
        var aviso = fila.querySelector('.f-aviso-tutor');
        if (aviso) { aviso.style.display = (fila._estYaTutor) ? 'block' : 'none'; aviso.textContent = 'Este estudiante ya cuenta con un tutor. Puede registrarlo como padre/madre únicamente.'; }
        var tipo = fila.querySelector('select[name$="[tipo_relacion]"]');
        if (tipo && fila._estYaTutor && (tipo.value === 'TUTOR')) { tipo.value = ''; }
        actualizarCorreoRequerido();
    }

    function quitarVinculacionEstudiante(btn) {
        var fila = btn.closest('.fila-estudiante');
        fila.querySelector('.f-est-id').value = '';
        var resumen = fila.querySelector('.estudiante-seleccionado');
        if (resumen) resumen.style.display = 'none';
        var aviso = fila.querySelector('.f-aviso-tutor');
        if (aviso) aviso.style.display = 'none';
        fila._estYaTutor = false;
        var input = fila.querySelector('.buscador-estudiante');
        if (input) input.focus();
        actualizarCorreoRequerido();
    }

    function validarTipoRelacion(sel) {
        var fila = sel.closest('.fila-estudiante');
        var aviso = fila.querySelector('.f-aviso-tutor');
        if (sel.value === 'TUTOR' && fila._estYaTutor) {
            if (aviso) { aviso.style.display = 'block'; aviso.textContent = 'Este estudiante ya cuenta con un tutor. Solo puede registrarlo como padre o madre.'; }
            sel.value = '';
        } else if (aviso && !fila._estYaTutor) {
            aviso.style.display = 'none';
        }
        actualizarCorreoRequerido();
    }

    function reordenarFilasEstudiantes(modo) {
        var cont = document.getElementById('filas-estudiantes-' + modo);
        if (!cont) return;
        var filas = cont.querySelectorAll('.fila-estudiante');
        filas.forEach(function (fila, idx) {
            var idInput = fila.querySelector('.f-est-id');
            if (idInput) idInput.name = 'students[' + idx + '][id]';
            var rel = fila.querySelector('select[name$="[tipo_relacion]"]');
            if (rel) rel.name = 'students[' + idx + '][tipo_relacion]';
        });
    }

    function nuevaFilaEstudiantes(modo, datos) {
        datos = datos || {};
        const contenedor = document.getElementById('filas-estudiantes-' + modo);
        const fila = document.createElement('div');
        fila.className = 'fila-estudiante mb-2';
        var idx = contenedor.children.length;
        var eSel = estudiantePorId(datos.id);
        var vinculoHtml = '';
        if (eSel) vinculoHtml = '<strong>' + (eSel.apellidos || '') + ' ' + (eSel.nombres || '') + '</strong>' + (eSel.codigo ? ' — ' + eSel.codigo : '');
        fila.innerHTML =
            '<div class="flex flex-col sm:flex-row gap-2">' +
                '<div class="flex-1">' +
                    '<input type="hidden" class="f-est-id" name="students[' + idx + '][id]" value="' + (datos.id || '') + '">' +
                    '<div style="position:relative;">' +
                        '<input type="text" class="buscador-estudiante w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" placeholder="Buscar estudiante por nombre o código..." autocomplete="off" onfocus="abrirBusquedaEstudiante(this)" oninput="buscarEstudiante(this)" value="">' +
                        '<div class="resultados-estudiantes" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:60; max-height:220px; overflow-y:auto; background:var(--card); border:1px solid var(--border); border-radius:0.5rem; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15);"></div>' +
                    '</div>' +
                    '<div class="estudiante-seleccionado" style="' + (eSel ? 'display:flex;' : 'display:none;') + ' align-items:center; justify-content:space-between; gap:0.5rem; margin-top:0.5rem; padding:0.5rem 0.75rem; border:1px solid var(--border); border-radius:0.5rem; background:var(--muted);">' +
                        '<span class="es-info" style="font-size:0.8125rem; color:var(--foreground);">' + vinculoHtml + '</span>' +
                        '<button type="button" onclick="quitarVinculacionEstudiante(this)" style="font-size:0.75rem; color:var(--error); cursor:pointer; background:none; border:none; text-decoration:underline;">Cambiar</button>' +
                    '</div>' +
                '</div>' +
                '<div class="w-full sm:w-40">' +
                    '<select name="students[' + idx + '][tipo_relacion]" onchange="validarTipoRelacion(this)" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm">' + opcionesTipo(datos.tipo, modo) + '</select>' +
                    '<p class="f-aviso-tutor" style="display:none; margin-top:0.375rem; font-size:0.6875rem; color:var(--warning);"></p>' +
                '</div>' +
                '<div class="shrink-0">' +
                    '<button type="button" onclick="quitarFilaEstudiante(this, \'' + modo + '\')" class="px-3 py-2.5 rounded-lg border border-error/20 text-error hover:bg-error/10 transition-colors cursor-pointer" title="Quitar">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                    '</button>' +
                '</div>' +
            '</div>';
        contenedor.appendChild(fila);
        fila._estYaTutor = datos.id ? CON_TUTOR_SET.has(String(datos.id)) : false;
        var aviso = fila.querySelector('.f-aviso-tutor');
        if (aviso && fila._estYaTutor) {
            aviso.style.display = 'block';
            aviso.textContent = 'Este estudiante ya cuenta con un tutor. Puede registrarlo como padre/madre únicamente.';
            var sel = fila.querySelector('select[name$="[tipo_relacion]"]');
            if (sel && (sel.value === 'TUTOR')) sel.value = '';
        }
    }

    function quitarFilaEstudiante(btn, modo) {
        btn.closest('.fila-estudiante').remove();
        reordenarFilasEstudiantes(modo);
        actualizarCorreoRequerido();
    }

    function agregarFilaEstudiantes(modo) {
        nuevaFilaEstudiantes(modo, {});
        actualizarCorreoRequerido();
    }

    function tieneTutorSeleccionado() {
        const filas = document.querySelectorAll('.fila-estudiante');
        for (const fila of filas) {
            const tipo = fila.querySelector('select[name$="[tipo_relacion]"]');
            if (tipo && tipo.value === 'TUTOR') return true;
        }
        return false;
    }

    function actualizarCorreoRequerido() {
        const hayTutor = tieneTutorSeleccionado();
        ['create', 'edit'].forEach(function (modo) {
            const input = document.getElementById('email-' + modo);
            const lbl = document.getElementById('lbl-email-' + modo);
            if (input) input.required = hayTutor;
            if (lbl) lbl.classList.toggle('hidden', !hayTutor);
        });
    }

    document.addEventListener('click', function (e) {
        if (!(e.target && e.target.closest && e.target.closest('.fila-estudiante'))) {
            document.querySelectorAll('.resultados-estudiantes').forEach(function (dd) { dd.style.display = 'none'; });
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.resultados-estudiantes').forEach(function (dd) { dd.style.display = 'none'; });
        }
    });
</script>