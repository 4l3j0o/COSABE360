<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla aún no tiene deleted_at (caso develop actual), agregarlo
        if (!Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }

        // Migrar datos existentes con estado ELIMINADO a soft delete
        if (Schema::hasColumn('students', 'estado')) {
            $eliminados = DB::table('students')->where('estado', 'ELIMINADO')->get();
            foreach ($eliminados as $est) {
                DB::table('students')->where('id', $est->id)->update([
                    'deleted_at' => now(),
                    'estado'     => 'INACTIVO',
                ]);
            }

            // En MySQL, modificar ENUM para quitar ELIMINADO; en SQLite lo ignora
            try {
                DB::statement("ALTER TABLE students MODIFY estado ENUM('ACTIVO','INACTIVO','EGRESADO','TRASLADADO') DEFAULT 'ACTIVO'");
            } catch (\Throwable $e) {
                // SQLite no soporta MODIFY — en tests se recrea desde cero con la migración corregida
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
        try {
            DB::statement("ALTER TABLE students MODIFY estado ENUM('ACTIVO','INACTIVO','EGRESADO','TRASLADADO','ELIMINADO') DEFAULT 'ACTIVO'");
        } catch (\Throwable $e) {
        }
    }
};
