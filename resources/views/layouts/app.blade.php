<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </script>
    @stack('modals')
</body>
</html>
