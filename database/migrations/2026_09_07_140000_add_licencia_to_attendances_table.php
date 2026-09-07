<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite no permite alterar un enum con CHECK. Reconstruye la tabla attendances
     * ampliando el estado con 'LICENCIA' (HU-50). Sin pérdida de datos.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return; // MySQL/PostgreSQL no requieren este paso
        }

        Schema::disableForeignKeyConstraints();

        $this->reconstruir('attendances', "
            CREATE TABLE attendances_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                course_id INTEGER NOT NULL,
                subject_id INTEGER NULL,
                fecha DATE NOT NULL,
                estado TEXT NOT NULL CHECK (estado IN ('PRESENTE','FALTA','ATRASO','PERMISO','JUSTIFICADO','LICENCIA')),
                justificacion TEXT NULL,
                registered_by_user_id INTEGER NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                CONSTRAINT attendances_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                CONSTRAINT attendances_course_id_foreign FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                CONSTRAINT attendances_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
                CONSTRAINT attendances_registered_by_user_id_foreign FOREIGN KEY (registered_by_user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        Schema::enableForeignKeyConstraints();
    }

    private function reconstruir(string $tabla, string $createSql): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');

        $columnas = DB::getSchemaBuilder()->getColumnListing($tabla);
        $lista = implode(', ', array_map(fn ($c) => '"'.$c.'"', $columnas));

        DB::statement('ALTER TABLE "'.$tabla.'" RENAME TO "'.$tabla.'_old"');
        DB::statement($createSql);
        DB::statement('INSERT INTO "'.$tabla.'_new" ('.$lista.') SELECT '.$lista.' FROM "'.$tabla.'_old"');
        DB::statement('DROP TABLE "'.$tabla.'_old"');
        DB::statement('ALTER TABLE "'.$tabla.'_new" RENAME TO "'.$tabla.'"');

        // Recrear índices
        DB::statement('CREATE UNIQUE INDEX uk_estudiante_fecha_curso_materia ON "'.$tabla.'" (student_id, fecha, course_id, subject_id)');
        DB::statement('CREATE INDEX idx_'.$tabla.'_course_id ON "'.$tabla.'" (course_id)');
        DB::statement('CREATE INDEX idx_'.$tabla.'_subject_id ON "'.$tabla.'" (subject_id)');
        DB::statement('CREATE INDEX idx_'.$tabla.'_fecha ON "'.$tabla.'" (fecha)');
        DB::statement('CREATE INDEX idx_'.$tabla.'_estado ON "'.$tabla.'" (estado)');

        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        // No se revierte automáticamente; revertir requiere reconstrucción previa.
    }
};
