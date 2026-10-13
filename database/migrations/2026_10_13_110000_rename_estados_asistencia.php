<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * HU-71: renombra los estados de asistencia.
     * FALTA→AUSENTE, ATRASO→RETRASO, PERMISO→LICENCIA,
     * JUSTIFICADO→RETIRO_JUSTIFICADO. PRESENTE y LICENCIA no cambian.
     */
    public function up(): void
    {
        DB::table('attendances')->where('estado', 'FALTA')->update(['estado' => 'AUSENTE']);
        DB::table('attendances')->where('estado', 'ATRASO')->update(['estado' => 'RETRASO']);
        DB::table('attendances')->where('estado', 'PERMISO')->update(['estado' => 'LICENCIA']);
        DB::table('attendances')->where('estado', 'JUSTIFICADO')->update(['estado' => 'RETIRO_JUSTIFICADO']);

        DB::statement("ALTER TABLE `attendances` MODIFY `estado` ENUM('PRESENTE','AUSENTE','LICENCIA','RETRASO','RETIRO_JUSTIFICADO') NOT NULL");
    }

    public function down(): void
    {
        DB::table('attendances')->where('estado', 'AUSENTE')->update(['estado' => 'FALTA']);
        DB::table('attendances')->where('estado', 'RETRASO')->update(['estado' => 'ATRASO']);
        DB::table('attendances')->where('estado', 'RETIRO_JUSTIFICADO')->update(['estado' => 'JUSTIFICADO']);

        DB::statement("ALTER TABLE `attendances` MODIFY `estado` ENUM('PRESENTE','FALTA','ATRASO','PERMISO','JUSTIFICADO','LICENCIA') NOT NULL");
    }
};
