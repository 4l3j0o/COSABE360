<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * HU-84 Sprint 1B: archivos privados.
 *
 * Los adjuntos con datos personales (fotos, documentos, evidencias, imágenes de
 * comunicados y del diario) se guardan en el disco "local" (storage/app/private)
 * y se sirven mediante una ruta autenticada que valida el acceso al registro.
 */
class ArchivoPrivado
{
    /** Elimina un archivo del disco privado y, si es legado, del disco público. */
    public static function eliminar(?string $path): void
    {
        if (! $path) {
            return;
        }

        foreach (['local', 'public'] as $disco) {
            $storage = Storage::disk($disco);
            if ($storage->exists($path)) {
                $storage->delete($path);
            }
        }
    }

    /** Ruta absoluta del archivo, buscando primero en privado y luego en legado público. */
    public static function ruta(string $path): ?string
    {
        foreach (['local', 'public'] as $disco) {
            $storage = Storage::disk($disco);
            if ($storage->exists($path)) {
                return $storage->path($path);
            }
        }

        return null;
    }

    /** URL autenticada para descargar/ver el archivo. */
    public static function url(string $path): string
    {
        return route('archivos.show', ['path' => $path]);
    }
}
