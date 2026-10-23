<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HU-85: middleware de permisos con compatibilidad temporal.
 *
 * Primero usa Spatie; si los permisos aún no fueron sembrados en un entorno,
 * recurre al catálogo config/permisos360.php usando el rol actual. Esto permite
 * migrar rutas sin romper instalaciones existentes ni la suite de tests.
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $requeridos = [];
        foreach ($permissions as $grupo) {
            foreach (explode('|', $grupo) as $permiso) {
                $permiso = trim($permiso);
                if ($permiso !== '') {
                    $requeridos[] = $permiso;
                }
            }
        }

        foreach ($requeridos as $permiso) {
            if ($user->can($permiso)) {
                return $next($request);
            }

            $modulo = array_search($permiso, config('permisos360.modulos', []), true);
            if ($modulo && $user->canAccess($modulo)) {
                return $next($request);
            }

            $rol = $user->rol?->nombre;
            $asignados = $rol ? (config("permisos360.roles.{$rol}", []) ?: []) : [];

            if (in_array('*', $asignados, true) || in_array($permiso, $asignados, true)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403, 'Acceso denegado: permiso insuficiente.');
        }

        return redirect()->route('acceso.denegado');
    }
}
