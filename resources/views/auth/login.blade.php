<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CSB 360</title>
    <link rel="icon" type="image/png" href="/images/logo-colegio-thumb.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-heading { font-family: 'Outfit', sans-serif; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .animate-shake { animation: shake 0.3s ease-in-out; }
    </style>
</head>
<body class="h-full bg-[#F5F5F5]">
    <div class="min-h-screen w-full flex overflow-hidden">
        <!-- Columna del formulario -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 z-10 bg-[#F5F5F5] overflow-y-auto">
            <div class="w-full max-w-md py-8">

                <!-- Logo y encabezado -->
                <div class="flex flex-col items-center text-center mb-12">
                    <div class="w-80 h-28 bg-white rounded-3xl flex items-center justify-center shadow-2xl shadow-green-900/10 p-4 mb-6 transition-all hover:scale-105 duration-500 border border-gray-100">
                        <img src="/images/logo-colegio.png" alt="CSB 360" class="w-full h-full object-contain">
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-4xl font-extrabold text-black tracking-tight leading-none uppercase font-heading">CSB 360</h1>
                        <p class="text-xs font-bold text-[#0E710E] uppercase tracking-[0.4em]">Sistema de Gestión Escolar</p>
                    </div>
                </div>

                <!-- Mensaje de error -->
                @if ($errors->any())
                    <div class="mb-8 bg-red-50 border border-red-100 rounded-2xl p-4 text-red-600 text-xs font-bold text-center animate-shake w-full max-w-sm mx-auto">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Card de acceso -->
                <div class="bg-white rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.08)] p-10 border border-gray-100 backdrop-blur-sm transition-all duration-500 flex flex-col items-center hover:shadow-[0_48px_80px_-20px_rgba(0,0,0,0.12)]">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Acceso con Google</span>

                    <div class="w-full">
                        <a href="{{ route('login.google') }}"
                           class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all duration-300 shadow-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-1.01.67-2.28 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05" />
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 6.27l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                            </svg>
                            Continuar con Google
                        </a>
                    </div>

                    <p class="mt-8 text-[11px] text-gray-400 font-medium text-center leading-relaxed">
                        Elija su cuenta de Google para continuar. <br />
                        Solo las cuentas <span class="text-gray-900">@cosabe.edu.bo</span> registradas tienen acceso.
                    </p>
                </div>

                <!-- Pie -->
                <div class="mt-12 text-center">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-loose">
                        &copy; {{ date('Y') }} CSB 360 <br />
                        <span class="text-gray-300">Sistema de Gestión Escolar</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Panel hero (solo escritorio) -->
        <div class="hidden lg:flex flex-1 relative overflow-hidden bg-[#0E710E]">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0E710E] via-[#1A8C1A] to-[#0a5a0a] opacity-90"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s"></div>

            <div class="relative z-10 w-full h-full flex flex-col items-center justify-center p-16 text-white text-center">
                <div class="max-w-lg space-y-8">
                    <div class="h-1 w-20 bg-emerald-400/50 rounded-full mx-auto"></div>
                    <h2 class="text-4xl lg:text-5xl font-bold leading-tight tracking-tight uppercase font-heading">Sistema de Gestión Escolar</h2>
                    <p class="text-xl text-emerald-100/90 font-light leading-relaxed">
                        Colegio San Bernardo de Tarija. Acceso exclusivo para personal, docentes y estudiantes de la institución.
                    </p>
                    <div class="pt-8 flex justify-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm"><i data-lucide="shield-check" class="w-6 h-6"></i></div>
                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm"><i data-lucide="database" class="w-6 h-6"></i></div>
                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm"><i data-lucide="users" class="w-6 h-6"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>