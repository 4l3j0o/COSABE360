<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-82: detalle del diario de aula.
     *
     * Una bitácora puede incluir varias actividades de clase, varias tareas
     * para el hogar y varios recursos (con imagen). Se retiran las columnas de
     * texto simple que se habían agregado como paso intermedio.
     */
    public function up(): void
    {
        Schema::create('diario_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diario_aula_id')->constrained('diario_aulas')->cascadeOnDelete();
            $table->string('periodo', 100)->nullable();
            $table->string('unidad_aprendizaje', 150)->nullable();
            $table->string('tema_estudio', 150)->nullable();
            $table->string('referencia_bibliografica', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('diario_tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diario_aula_id')->constrained('diario_aulas')->cascadeOnDelete();
            $table->string('titulo_obra', 150)->nullable();
            $table->string('paginas_referencia', 100)->nullable();
            $table->string('forma_presentacion', 150)->nullable();
            $table->date('fecha')->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('diario_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diario_aula_id')->constrained('diario_aulas')->cascadeOnDelete();
            $table->string('nombre', 150)->nullable();
            $table->string('imagen_path', 255)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('diario_aulas', function (Blueprint $table) {
            $table->dropColumn(['actividades_clase', 'actividades_hogar', 'recursos']);
        });
    }

    public function down(): void
    {
        Schema::table('diario_aulas', function (Blueprint $table) {
            $table->text('actividades_clase')->nullable()->after('avance_curricular');
            $table->text('actividades_hogar')->nullable()->after('actividades_clase');
            $table->text('recursos')->nullable()->after('actividades_hogar');
        });

        Schema::dropIfExists('diario_recursos');
        Schema::dropIfExists('diario_tareas');
        Schema::dropIfExists('diario_actividades');
    }
};
