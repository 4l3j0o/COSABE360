<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: Inscripción en Línea paso 2 (ubicación del domicilio + fachada).
     */
    public function up(): void
    {
        Schema::table('student_rude_records', function (Blueprint $table) {
            $table->decimal('dir_latitud', 10, 7)->nullable()->after('dir_celular');
            $table->decimal('dir_longitud', 10, 7)->nullable()->after('dir_latitud');
            $table->string('foto_fachada', 500)->nullable()->after('dir_longitud');
        });
    }

    public function down(): void
    {
        Schema::table('student_rude_records', function (Blueprint $table) {
            $table->dropColumn(['dir_latitud', 'dir_longitud', 'foto_fachada']);
        });
    }
};
