<div class="p-5 border-b border-border">
    <h1 class="text-lg font-bold text-primary">U.E. San Bernardo</h1>
    <p class="text-xs text-muted-foreground mt-0.5">Sistema de Gestión Escolar</p>
</div>

<nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    @yield('sidebar')
</nav>

<div class="p-4 border-t border-border">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-sm shrink-0">
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
        <button type="submit" class="w-full text-left text-sm text-error hover:bg-error/10 px-3 py-2 rounded-lg transition-colors cursor-pointer">
            Cerrar sesión
        </button>
    </form>
</div>
