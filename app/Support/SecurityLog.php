<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * HU-87: eventos de seguridad en un canal separado.
 * No guardar contraseñas, tokens ni contenido de archivos.
 */
class SecurityLog
{
    public static function event(string $evento, array $contexto = []): void
    {
        Log::channel('security')->info($evento, array_merge([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'ruta' => request()->path(),
        ], $contexto));
    }
}
