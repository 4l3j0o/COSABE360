<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-82: el diario de aula separa las actividades de clase, las tareas
     * para el hogar y los recursos utilizados.
     */
    public function up(): void
    {
        Schema::table('diario_aulas', function (Blueprint $table) {
            $table->renameColumn('ejercicios', 'actividades_clase');
        });

        Schema::table('diario_aulas', function (Blueprint $table) {
            $table->text('actividades_hogar')->nullable()->after('actividades_clase');
            $table->text('recursos')->nullable()->after('actividades_hogar');
        });
    }

    public function down(): void
    {
        Schema::table('diario_aulas', function (Blueprint $table) {
            $table->dropColumn(['actividades_hogar', 'recursos']);
        });

        Schema::table('diario_aulas', function (Blueprint $table) {
            $table->renameColumn('actividades_clase', 'ejercicios');
        });
    }
};
