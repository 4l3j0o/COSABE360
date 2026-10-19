<?php

namespace App\Support;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * HU-82: política única de imágenes del sistema.
 *
 * Toda foto subida (comunicados, licencias, estudiantes, inscripciones) se
 * convierte a .webp y se reescala a un lado máximo para reducir peso. Se guarda
 * siempre en el disco "public" y se devuelve la ruta relativa (lo que se
 * persiste en la columna correspondiente).
 */
class ImagenWebp
{
    /**
     * Convierte y guarda una imagen subida como .webp.
     *
     * @param  string  $directorio  carpeta dentro del disco "public" (ej. "licencias")
     * @param  string  $prefijo  prefijo del nombre de archivo (ej. "lic_")
     * @return string ruta relativa (ej. "licencias/lic_<uuid>.webp")
     */
    public function guardar(
        UploadedFile $archivo,
        string $directorio,
        string $prefijo = 'img_',
        int $maxLado = 1600,
        int $calidad = 80,
    ): string {
        $contenido = @file_get_contents($archivo->getRealPath());

        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el archivo de imagen.');
        }

        $imagen = @imagecreatefromstring($contenido);

        if ($imagen === false) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $imagen = $this->redimensionar($imagen, $maxLado);

        $tmp = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($imagen, $tmp, $calidad);
        imagedestroy($imagen);

        $nombre = $prefijo.Str::uuid().'.webp';
        Storage::disk('public')->putFileAs($directorio, new File($tmp), $nombre);
        @unlink($tmp);

        return trim($directorio, '/').'/'.$nombre;
    }

    /** Reescala proporcionalmente si el lado mayor supera $maxLado. */
    private function redimensionar($imagen, int $maxLado)
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);

        if (max($ancho, $alto) <= $maxLado) {
            return $imagen;
        }

        $factor = $maxLado / max($ancho, $alto);
        $nueva = imagecreatetruecolor((int) round($ancho * $factor), (int) round($alto * $factor));
        imagealphablending($nueva, false);
        imagesavealpha($nueva, true);
        imagecopyresampled($nueva, $imagen, 0, 0, 0, 0, imagesx($nueva), imagesy($nueva), $ancho, $alto);
        imagedestroy($imagen);

        return $nueva;
    }
}
