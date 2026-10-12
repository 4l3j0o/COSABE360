<?php

namespace Database\Seeders;

use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRudeRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // El dump trae varias sentencias (DELETE + INSERT por tabla). Se quitan los
        // comentarios y se ejecutan una por una para no exceder max_allowed_packet
        // ni depender de multi-statement en el driver.
        // Se desactivan las FK durante la importación: el dump conserva IDs
        // originales y este era el comportamiento efectivo en SQLite.
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $sentencias = preg_split('/;\s*\n/', $sql);

        Schema::disableForeignKeyConstraints();
        $ejecutadas = 0;
        try {
            foreach ($sentencias as $sentencia) {
                $sentencia = trim($sentencia);
                if ($sentencia === '') {
                    continue;
                }
                DB::unprepared($sentencia);
                $ejecutadas++;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->command->info('Datos de estudiantes 2026 importados ('.$ejecutadas.' sentencias).');
        $this->command->info('Estudiantes: '.Student::count());
        $this->command->info('Tutores: '.ParentGuardian::count());
        $this->command->info('Matrículas: '.Registration::count());
        $this->command->info('Registros RUDE: '.StudentRudeRecord::count());
    }
}
