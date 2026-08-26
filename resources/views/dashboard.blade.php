<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - San Bernardo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground min-h-screen font-sans">

    <header class="bg-card border-b border-border shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <h1 class="text-lg font-bold text-primary">U.E. San Bernardo</h1>

            <div class="flex items-center gap-4">
                <span class="text-sm text-muted-foreground">
                    {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
                </span>
                <span class="text-xs bg-accent text-accent-foreground px-2.5 py-1 rounded-full font-medium">
                    {{ auth()->user()->rol->nombre }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="text-sm text-error hover:underline cursor-pointer"
                    >
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-foreground mb-2">Bienvenido, {{ auth()->user()->nombre }}</h2>
            <p class="text-muted-foreground">Has iniciado sesión como <strong>{{ auth()->user()->rol->nombre }}</strong>.</p>
        </div>
    </main>

</body>
</html>
