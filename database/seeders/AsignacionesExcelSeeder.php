<?php

namespace Database\Seeders;

use App\Services\AsignacionImportService;
use Illuminate\Database\Seeder;

/**
 * HU-59: asignaciones curso-materia-docente desde el Excel oficial 2026
 * (database/data/docentes_20261.xlsx). Idempotente; el Excel manda.
 * Debe correr DESPUÉS de NormalizarMateriasSeeder (materias canónicas).
 */
class AsignacionesExcelSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/docentes_20261.xlsx');
        if (! is_file($path)) {
            $this->command->warn('Sin archivo docentes_20261.xlsx, se omite.');

            return;
        }

        $stats = app(AsignacionImportService::class)->importar($path);

        $this->command->info(
            "Asignaciones Excel: {$stats['creadas']} creadas, {$stats['actualizadas']} actualizadas, {$stats['existentes']} sin cambios."
        );
        foreach ($stats['omitidas'] as $msg) {
            $this->command->warn($msg);
        }
    }
}
