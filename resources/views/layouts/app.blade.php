<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'San Bernardo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground min-h-screen font-sans flex">

    <div id="sidebar-backdrop" onclick="closeSidebar()"></div>

    <div id="sidebar-desktop" class="app-sidebar">
        @include('layouts._sidebar-content')
    </div>

    <div id="sidebar-mobile-wrap">
        <aside class="app-sidebar">
            @include('layouts._sidebar-content')
        </aside>
    </div>

    <div class="app-main flex-1 min-h-screen flex flex-col min-w-0">
        <header class="bg-white border-b border-border h-14 flex items-center px-4 md:px-6 sticky top-0 z-30">
            <button id="btn-menu" type="button" onclick="openSidebar()" class="mr-3 p-1.5 rounded-lg hover:bg-muted transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h2 class="text-base font-semibold text-foreground">@yield('titulo', 'Dashboard')</h2>
        </header>

        <main class="flex-1 p-4 md:p-6">
            @if(session('mensaje'))
                <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">
                    {{ session('mensaje') }}
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>

    @stack('scripts')

    <script>
        function openSidebar() {
            document.getElementById('sidebar-mobile-wrap').classList.add('open');
            document.getElementById('sidebar-backdrop').classList.add('open');
        }
        function closeSidebar() {
            document.getElementById('sidebar-mobile-wrap').classList.remove('open');
            document.getElementById('sidebar-backdrop').classList.remove('open');
        }
        function openModal(id) {
            var el = document.getElementById(id);
            el.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            var el = document.getElementById(id);
            el.style.display = 'none';
            document.body.style.overflow = '';
        }

        function pickNormalizar(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); }
        function pickItemVisible(it) { return it.style.display !== 'none'; }

        function pickAplicaFiltro(id) {
            var lista = document.getElementById('pick-' + id + '-lista');
            var input = document.getElementById('pick-' + id + '-input');
            if (!lista || !input) return;
            var items = Array.prototype.slice.call(lista.querySelectorAll('.pick-item'));
            var q = pickNormalizar(input.value);

            var filtroExtra = (typeof window.pickFiltroExtra === 'function') ? window.pickFiltroExtra : null;

            // Resultados que pasan el filtro (búsqueda + filtro extra), en orden
            var bloques = [];
            for (var i = 0; i < items.length; i++) {
                var it = items[i];
                var coincide = !q || (pickNormalizar(it.getAttribute('data-search')).indexOf(q) !== -1);
                var extra = filtroExtra ? filtroExtra(id, it) : true;
                if (coincide && extra) bloques.push(it);
            }

            // Tope inicial: 5 por defecto, configurable por picker (data-tope).
            // Si se está escribiendo, mostramos todos los que coincidan (con scroll).
            var tope = bloques.length;
            if (!q) {
                var t = parseInt(lista.getAttribute('data-tope'), 10);
                if (!t || t < 1) t = 5;
                tope = Math.min(t, bloques.length);
            }

            // Ocultamos todos y mostramos (tope) items inicialmente; el resto queda
            // oculto y se revela conforme el usuario baja con el scroll.
            for (var o = 0; o < items.length; o++) {
                items[o].style.display = 'none';
            }
            for (var b = 0; b < bloques.length; b++) {
                bloques[b].style.display = (b < tope) ? '' : 'none';
            }
            lista._pickBatch = bloques;
            lista._pickTope = tope;
            lista._pickId = id;

            // Vincular scroll progresivo (revelar más al llegar al fondo)
            if (!lista._scrollVinculado) {
                lista._scrollVinculado = true;
                lista.addEventListener('scroll', function () {
                    if (lista.scrollTop + lista.clientHeight >= lista.scrollHeight - 10) {
                        var guardados = lista._pickBatch || [];
                        var tp = lista._pickTope || 0;
                        if (tp < guardados.length) {
                            var nuevoTope = Math.min(tp + 10, guardados.length);
                            for (var nb = tp; nb < nuevoTope; nb++) {
                                if (guardados[nb].style.display === 'none') guardados[nb].style.display = '';
                            }
                            lista._pickTope = nuevoTope;
                        }
                    }
                });
            }

            // Altura del recuadro: 220px si hay hay más que caben, sino ajustada.
            var altoNecesario = bloques.length * 60;
            lista.style.height = (altoNecesario < 1 ? 1 : (altoNecesario > 220 ? 220 : altoNecesario)) + 'px';

            var empty = lista.querySelector('.pick-empty');
            if (empty) empty.style.display = bloques.length === 0 ? '' : 'none';
            lista.style.display = 'block';
        }

        function pickSeleccionar(id, el) {
            var value = document.getElementById('pick-' + id + '-value');
            var input = document.getElementById('pick-' + id + '-input');
            var lista = document.getElementById('pick-' + id + '-lista');
            if (value) value.value = el.getAttribute('data-value');
            if (input) {
                input.value = el.getAttribute('data-label');
                input.setAttribute('data-selected', '1');
            }
            if (lista) lista.style.display = 'none';
            if (window.pickExtraSeleccionar) window.pickExtraSeleccionar(id);
            return false;
        }

        function setPickerValor(id, valor) {
            var v = document.getElementById('pick-' + id + '-value');
            var input = document.getElementById('pick-' + id + '-input');
            var lista = document.getElementById('pick-' + id + '-lista');
            if (v) v.value = valor || '';
            var label = '';
            if (valor && lista) {
                var it = lista.querySelector('.pick-item[data-value="' + valor + '"]');
                if (it) label = it.getAttribute('data-label');
            }
            if (input) {
                input.value = label;
                if (label) input.setAttribute('data-selected', '1');
                else input.removeAttribute('data-selected');
            }
        }

        function pickIdDeInput(el) { return el.id.replace(/^pick-|-input$/, ''); }

        function pickTecla(id, e) {
            var lista = document.getElementById('pick-' + id + '-lista');
            if (!lista) return;
            if (e.key === 'Escape') { lista.style.display = 'none'; return; }
            if (e.key === 'Enter') {
                var items = lista.querySelectorAll('.pick-item');
                for (var i = 0; i < items.length; i++) {
                    if (pickItemVisible(items[i])) { pickSeleccionar(id, items[i]); e.preventDefault(); document.getElementById('pick-' + id + '-input').blur(); break; }
                }
            }
        }

        function pickInit() {
            var pickers = document.querySelectorAll('[id^="pick-"][id$="-input"]');
            Array.prototype.forEach.call(pickers, function (input) {
                if (input.getAttribute('data-inicializado')) return;
                var id = pickIdDeInput(input);
                var lista = document.getElementById('pick-' + id + '-lista');
                if (!lista) return;
                input.setAttribute('data-inicializado', '1');
                input.addEventListener('focus', function () { pickAplicaFiltro(id); });
                input.addEventListener('input', function () { pickAplicaFiltro(id); });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            pickInit();
            setTimeout(pickInit, 300);
            setTimeout(pickInit, 800);
        });
        window.addEventListener('load', function () { setTimeout(pickInit, 0); });

        function resetaPickers() {
            var values = document.querySelectorAll('input[id^="pick-"][id$="-value"]');
            Array.prototype.forEach.call(values, function (v) {
                var id = v.id.replace(/^pick-|-value$/, '');
                var input = document.getElementById('pick-' + id + '-input');
                var lista = document.getElementById('pick-' + id + '-lista');
                v.value = '';
                if (input) { input.value = ''; input.removeAttribute('data-selected'); }
                if (lista) {
                    var items = lista.querySelectorAll('.pick-item');
                    Array.prototype.forEach.call(items, function (it, i) { it.style.display = i < 5 ? '' : 'none'; });
                    var empty = lista.querySelector('.pick-empty');
                    if (empty) empty.style.display = 'none';
                    lista.style.display = 'none';
                }
            });
        }
    </script>
    @stack('modals')
</body>
</html>
