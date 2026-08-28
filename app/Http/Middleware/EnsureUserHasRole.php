<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $roles = array_map('trim', explode(',', $role));

        if (! in_array(auth()->user()->rol->nombre, $roles, true)) {
            return redirect()->route('acceso.denegado');
        }

        return $next($request);
    }
}
