<?php

namespace Database\Seeders;

use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRudeRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstudiantesRealesSeeder extends Seeder
{
    /**
     * Importa los datos reales de estudiantes, tutores, matrículas y RUDE 2026
     * a partir del dump SQL incluido (database/data/estudiantes_2026.sql).
     * Preserva los IDs originales para mantener las relaciones entre tablas.
     *
     * Nota: contiene datos personales (PII). Solo ejecutar en entornos privados.
     */
    public function run(): void
    {
        $archivo = database_path('data/estudiantes_2026.sql');

        if (! file_exists($archivo)) {
            $this->command->error('No se encontró el archivo de datos: '.$archivo);

            return;
        }

        $sql = file_get_contents($archivo);

        // El dump ejecuta DELETE antes de cada tabla (idempotente).
        DB::unprepared($sql);

        $this->command->info('Datos de estudiantes 2026 importados.');
        $this->command->info('Estudiantes: '.Student::count());
        $this->command->info('Tutores: '.ParentGuardian::count());
        $this->command->info('Matrículas: '.Registration::count());
        $this->command->info('Registros RUDE: '.StudentRudeRecord::count());
    }
}
