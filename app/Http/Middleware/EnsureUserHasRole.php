<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next,string $role): Response
    {
        if (! auth()->check())
        {
            return redirect()->route('login');
        }

        if (auth()->user()->rol->nombre !== $role)
        {
            return redirect()->route('acceso.denegado');
        }

        return $next($request);

        
    }
}
