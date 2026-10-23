<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * HU-84 Sprint 1B: migra adjuntos legados del disco público al privado.
 *
 * Ejecutar una vez en cada entorno después de desplegar:
 *   php artisan archivos:privatizar --dry-run
 *   php artisan archivos:privatizar
 */
class PrivatizarArchivos extends Command
{
    protected $signature = 'archivos:privatizar {--dry-run : Solo muestra los archivos que se moverían}';

    protected $description = 'Mueve los archivos de storage/app/public a storage/app/private';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $archivos = array_values(array_filter(
            $public->allFiles(),
            fn (string $path) => ! str_starts_with($path, '.')
        ));

        if ($archivos === []) {
            $this->info('No hay archivos en el disco público.');

            return self::SUCCESS;
        }

        foreach ($archivos as $path) {
            if ($this->option('dry-run')) {
                $this->line($path);

                continue;
            }

            $private->put($path, $public->get($path));
            $public->delete($path);
        }

        $this->info($this->option('dry-run')
            ? 'Archivos que se moverían: '.count($archivos)
            : 'Archivos movidos al disco privado: '.count($archivos));

        return self::SUCCESS;
    }
}
