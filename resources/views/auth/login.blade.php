<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - San Bernardo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground min-h-screen flex items-center justify-center font-sans">

    <div class="w-full max-w-md px-4">
        <div class="bg-card border border-border rounded-2xl shadow-lg p-8 space-y-6">

            <div class="text-center space-y-2">
                <h1 class="text-2xl font-bold text-primary">U.E. San Bernardo</h1>
                <p class="text-sm text-muted-foreground">Sistema de Gestión Escolar</p>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf

                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-medium text-foreground">Correo electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-2.5 border border-input rounded-lg bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        placeholder="admin@sanber.edu.bo"
                    >
                    @error('email')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-sm font-medium text-foreground">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-2.5 border border-input rounded-lg bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full bg-primary text-primary-foreground font-semibold py-2.5 px-4 rounded-lg hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer"
                >
                    Iniciar sesión
                </button>
            </form>

        </div>

        <p class="text-center text-xs text-muted-foreground mt-6">
            &copy; {{ date('Y') }} Unidad Educativa San Bernardo
        </p>
    </div>

</body>
</html>
