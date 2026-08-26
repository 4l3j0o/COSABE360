<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Sanber') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <header class="border-b border-[#19140035] dark:border-[#3E3E3A] bg-white/80 dark:bg-[#161615]/80 backdrop-blur sticky top-0 z-10">
        <nav class="max-w-5xl mx-auto px-6 py-3 flex items-center justify-between text-sm">
            <a href="{{ url('/') }}" class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Sanber</a>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('users.index') }}"
                       class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition">
                        Usuarios
                    </a>
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">{{ auth()->user()->nombre }} ({{ auth()->user()->rol?->nombre }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-block px-4 py-1.5 border border-[#19140035] dark:border-[#3E3E3A] hover:border-[#1915014a] dark:hover:border-[#62605b] rounded-sm transition">
                            Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-block px-4 py-1.5 border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm transition">
                        Iniciar sesión
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-sm border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950 px-4 py-3 text-sm text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-sm border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
