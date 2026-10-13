<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-70: horario de visita para padres. Los bloques pueden ser de CLASE
     * o de VISITA (atención a padres). Las vistas de clases solo muestran
     * bloques CLASE; la visita se gestiona y consulta aparte.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->enum('tipo', ['CLASE', 'VISITA'])->default('CLASE')->after('teacher_id');
            $table->index('tipo', 'schedules_tipo_index');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_tipo_index');
            $table->dropColumn('tipo');
        });
    }
};
