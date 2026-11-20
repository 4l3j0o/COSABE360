<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('titulo') - CSB 360</title>
    @vite('resources/css/app.css')
</head>
<body class="h-full bg-background text-foreground antialiased">
    <main class="min-h-full flex items-center justify-center p-6">
        <div class="w-full max-w-lg bg-card border border-border rounded-2xl shadow-xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto mb-5 text-lg font-bold">
                @yield('code')
            </div>
            <h1 class="text-2xl font-bold mb-2">@yield('titulo')</h1>
            <p class="text-sm text-muted-foreground leading-relaxed mb-6">@yield('mensaje')</p>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al inicio
            </a>
        </div>
    </main>
</body>
</html>
