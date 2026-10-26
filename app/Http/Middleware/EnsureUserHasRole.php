<?php

namespace App\Http\Middleware;

use App\Support\SecurityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * HU05: Control de permisos por rol
     * - Deniega acceso a módulos no autorizados
     * - Redirige a /acceso-denegado si URL no autorizada
     * - Soporta múltiples roles separados por coma: role:Administrador,Secretaría
     * - Ocultar menús/botones se maneja en Blade con hasAnyRole()/canAccess()
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

$user = auth()->user();

        // Soporta "Administrador,Secretaría" como un solo parámetro
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        // Carga relación rol si no está cargada
        if (! $user->relationLoaded('rol')) {
            $user->load('rol');
        }

        $userRole = $user->rol?->nombre;

        if (! in_array($userRole, $allowedRoles, true)) {
            SecurityLog::event('access.denied.role', [
                'rol' => $userRole,
                'roles_permitidos' => $allowedRoles,
            ]);

            // Si es petición AJAX/JSON devolver 403, sino redirigir a acceso denegado
            if ($request->expectsJson()) {
                abort(403, 'Acceso denegado: rol no autorizado.');
            }

            return redirect()->route('acceso.denegado');
        }

        return $next($request);
    }
}
