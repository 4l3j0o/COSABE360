<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * HU-82 (Opción B): las listas por materia son la única fuente de verdad.
 * Se eliminan las filas "generales" de asistencia (subject_id NULL) creadas
 * por el Regente, que duplicaban el registro del día y descuadraban los reportes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('attendances')->whereNull('subject_id')->delete();
    }

    public function down(): void
    {
        // No se pueden reconstruir las filas generales eliminadas.
    }
};
