<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // Soporta tanto "role:Administrador,Director" (uno con comas) como "role:Administrador" "role:Director" separados
        $all = collect($roles)
            ->flatMap(fn ($r) => explode(',', $r))
            ->map(fn ($r) => trim($r))
            ->filter()
            ->toArray();

        if (! in_array(auth()->user()->rol->nombre, $all, true)) {
            return redirect()->route('acceso.denegado');
        }

        return $next($request);
    }
}
