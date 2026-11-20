<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-68: limpieza final del retiro del rol tutor + soporte MySQL.
     *
     * 1) Elimina `parent_guardians.user_id` (los tutores ya no tienen cuenta).
     *    En MySQL se puede soltar el FK y la columna directamente.
     * 2) Corrige el enum de `announcement_recipients.tipo_destinatario`:
     *    quita el alcance 'PADRES' (ya no existe) y agrega 'NIVEL_DOCENTES',
     *    que el código ya usaba pero faltaba en el enum (fallaba en MySQL).
     */
    public function up(): void
    {
        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // MySQL valida los largos (SQLite no). Los datos reales traen tutores con
        // textos largos en documento/teléfono (direcciones mal capturadas, ~51 filas):
        // se amplían para no perder ni rechazar esos registros.
        // Además, MySQL compara sin distinguir mayúsculas/acentos por defecto, lo que
        // genera duplicados que SQLite (byte a byte) no detectaba; se usa collation
        // binaria para conservar el comportamiento original.
        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->string('numero_documento', 60)->collation('utf8mb4_bin')->change();
            $table->string('telefono', 60)->nullable()->change();
        });

        Schema::table('announcement_recipients', function (Blueprint $table) {
            $table->enum('tipo_destinatario', ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES'])
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('announcement_recipients', function (Blueprint $table) {
            $table->enum('tipo_destinatario', ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'])
                ->change();
        });
    }
};
