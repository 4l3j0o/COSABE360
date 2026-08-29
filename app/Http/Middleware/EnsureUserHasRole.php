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

        // Soporte para "role:Admin,Secretaría" (variadic) y "role:Admin,Secretaría" como string único
        $roles = collect($roles)->flatMap(fn ($r) => explode(',', $r))->map(fn ($r) => trim($r))->filter()->toArray();
        $userRole = auth()->user()->rol?->nombre ?? 'NULL';

        if (! in_array($userRole, $roles, true)) {
            return redirect()->route('acceso.denegado');
        }

        return $next($request);
    }
}
