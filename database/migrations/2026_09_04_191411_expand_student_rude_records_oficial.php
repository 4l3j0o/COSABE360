<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ampliación de student_rude_records para el formulario RUDE oficial 2025
     * (Resolución Ministerial Nº 0031/2025). Secciones I, II (2.9), III y IV.
     * La Sección V reutiliza la tabla parent_guardians vía student_parent.
     */
    public function up(): void
    {
        Schema::table('student_rude_records', function (Blueprint $table) {
            // --- Sección I: DATOS DE LA UNIDAD EDUCATIVA ---
            $table->date('fecha_registro')->nullable()->after('codigo_sie_ue');
            $table->string('ue_localidad', 150)->nullable();
            $table->string('ue_nombres', 150)->nullable();
            $table->string('ue_apellido_paterno', 100)->nullable();
            $table->string('ue_apellido_materno', 100)->nullable();
            $table->string('ue_pais', 100)->nullable();
            $table->string('ue_departamento', 100)->nullable();
            $table->string('ue_provincia', 100)->nullable();
            $table->string('ue_municipio', 100)->nullable();
            $table->string('ue_comunidad', 150)->nullable();
            $table->string('ue_zona', 150)->nullable();
            $table->string('ue_calle', 150)->nullable();
            $table->string('ue_nro_vivienda', 50)->nullable();
            $table->string('ue_telefono', 50)->nullable();
            $table->string('ue_celular', 50)->nullable();

            // --- Sección II: 2.9 Registro de discapacidad IBC ---
            $table->string('nro_registro_discapacidad', 100)->nullable();

            // --- Sección III: DIRECCIÓN ACTUAL ---
            $table->string('dir_departamento', 100)->nullable();
            $table->string('dir_provincia', 100)->nullable();
            $table->string('dir_municipio', 100)->nullable();
            $table->string('dir_comunidad', 150)->nullable();
            $table->string('dir_zona', 150)->nullable();
            $table->string('dir_calle', 150)->nullable();
            $table->string('dir_nro_vivienda', 50)->nullable();
            $table->string('dir_telefono', 50)->nullable();
            $table->string('dir_celular', 50)->nullable();

            // --- Sección IV.1: Idioma y autoidentificación ---
            $table->string('idioma_aprendio', 100)->nullable();
            $table->json('idiomas_uso_frecuente')->nullable();
            $table->string('autoidentificacion_cultural', 150)->nullable();

            // --- Sección IV.2: Salud ---
            $table->boolean('salud_centro_cercano')->nullable();
            $table->boolean('salud_atendido_anio')->nullable();
            $table->unsignedSmallInteger('salud_veces')->nullable();
            $table->string('salud_seguro', 100)->nullable();

            // --- Sección IV.3: Servicios básicos ---
            $table->boolean('serv_agua_red')->nullable();
            $table->string('serv_bano', 100)->nullable();
            $table->boolean('serv_alcantarillado')->nullable();
            $table->boolean('serv_electricidad')->nullable();
            $table->boolean('serv_recojo_basura')->nullable();
            $table->string('serv_vivienda_tipo', 100)->nullable();

            // --- Sección IV.4: Internet ---
            $table->json('internet_lugares')->nullable();
            $table->string('internet_frecuencia', 100)->nullable();

            // --- Sección IV.5: Actividad laboral ---
            $table->boolean('laboral_trabajo')->nullable();
            $table->string('laboral_actividad', 150)->nullable();
            $table->json('laboral_turnos')->nullable();
            $table->string('laboral_frecuencia', 100)->nullable();
            $table->boolean('laboral_recibio_pago')->nullable();

            // --- Sección IV.6: Transporte ---
            $table->string('transporte_medio', 100)->nullable();
            $table->string('transporte_tiempo', 100)->nullable();

            // --- Sección IV.7: Abandono escolar ---
            $table->boolean('abandono_escolar')->nullable();
            $table->json('abandono_razones')->nullable();

            // --- Sección IV.8: Características particulares ---
            $table->json('discapacidad_tipos')->nullable();
            $table->string('discapacidad_grado', 100)->nullable();
            $table->json('dificultad_aprendizaje')->nullable();

            // --- Sección V.1: Con quién vive ---
            $table->json('convive_con')->nullable();
        });
    }

    /**
     * Reverse the migrations: elimina únicamente las columnas añadidas.
     */
    public function down(): void
    {
        Schema::table('student_rude_records', function (Blueprint $table) {
            $columns = [
                'fecha_registro', 'ue_localidad', 'ue_nombres', 'ue_apellido_paterno',
                'ue_apellido_materno', 'ue_pais', 'ue_departamento', 'ue_provincia',
                'ue_municipio', 'ue_comunidad', 'ue_zona', 'ue_calle', 'ue_nro_vivienda',
                'ue_telefono', 'ue_celular', 'nro_registro_discapacidad',
                'dir_departamento', 'dir_provincia', 'dir_municipio', 'dir_comunidad',
                'dir_zona', 'dir_calle', 'dir_nro_vivienda', 'dir_telefono', 'dir_celular',
                'idioma_aprendio', 'idiomas_uso_frecuente', 'autoidentificacion_cultural',
                'salud_centro_cercano', 'salud_atendido_anio', 'salud_veces', 'salud_seguro',
                'serv_agua_red', 'serv_bano', 'serv_alcantarillado', 'serv_electricidad',
                'serv_recojo_basura', 'serv_vivienda_tipo', 'internet_lugares', 'internet_frecuencia',
                'laboral_trabajo', 'laboral_actividad', 'laboral_turnos', 'laboral_frecuencia',
                'laboral_recibio_pago', 'transporte_medio', 'transporte_tiempo',
                'abandono_escolar', 'abandono_razones', 'discapacidad_tipos', 'discapacidad_grado',
                'dificultad_aprendizaje', 'convive_con',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('student_rude_records', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
