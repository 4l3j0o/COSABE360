<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'San Bernardo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground min-h-screen font-sans flex">

    <aside class="w-64 bg-card border-r border-border min-h-screen flex flex-col shadow-sm">
        <div class="p-5 border-b border-border">
            <h1 class="text-lg font-bold text-primary">U.E. San Bernardo</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Sistema de Gestión Escolar</p>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            @yield('sidebar')
        </nav>

        <div class="p-4 border-t border-border">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-sm">
                    {{ substr(auth()->user()->nombre, 0, 1) }}{{ substr(auth()->user()->apellido, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-foreground truncate">
                        {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
                    </p>
                    <p class="text-xs text-muted-foreground truncate">
                        {{ auth()->user()->rol->nombre }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full text-left text-sm text-error hover:bg-error/10 px-3 py-2 rounded-lg transition-colors cursor-pointer"
                >
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 min-h-screen flex flex-col">
        <header class="bg-card border-b border-border h-14 flex items-center px-6">
            <h2 class="text-base font-semibold text-foreground">@yield('titulo', 'Dashboard')</h2>
        </header>

        <main class="flex-1 p-6">
            @if(session('mensaje'))
                <div class="mb-4 bg-success/10 border border-success/30 text-success px-4 py-3 rounded-lg text-sm">
                    {{ session('mensaje') }}
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>

</body>
</html>
