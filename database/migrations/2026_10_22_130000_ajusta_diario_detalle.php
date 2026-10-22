<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-82: ajustes al detalle del diario de aula.
     * - Actividades: se retiran periodo y unidad de aprendizaje.
     * - Tareas: se retira el título de la obra.
     * - Recursos: se admite un enlace de referencia además de la imagen.
     */
    public function up(): void
    {
        Schema::table('diario_actividades', function (Blueprint $table) {
            $table->dropColumn(['periodo', 'unidad_aprendizaje']);
        });

        Schema::table('diario_tareas', function (Blueprint $table) {
            $table->dropColumn('titulo_obra');
        });

        Schema::table('diario_recursos', function (Blueprint $table) {
            $table->string('link', 500)->nullable()->after('imagen_path');
        });
    }

    public function down(): void
    {
        Schema::table('diario_recursos', function (Blueprint $table) {
            $table->dropColumn('link');
        });

        Schema::table('diario_tareas', function (Blueprint $table) {
            $table->string('titulo_obra', 150)->nullable()->after('diario_aula_id');
        });

        Schema::table('diario_actividades', function (Blueprint $table) {
            $table->string('periodo', 100)->nullable()->after('diario_aula_id');
            $table->string('unidad_aprendizaje', 150)->nullable()->after('periodo');
        });
    }
};
