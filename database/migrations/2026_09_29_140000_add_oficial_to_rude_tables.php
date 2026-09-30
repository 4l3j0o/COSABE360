<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: campos del formulario oficial (2.5.1/2.5.2 extranjeros,
     * meses trabajados, transporte otro, centro de acogida, tutor extraordinario).
     */
    public function up(): void
    {
        Schema::table('student_rude_records', function (Blueprint $table) {
            $table->string('tipo_doc_extranjero', 100)->nullable()->after('nro_registro_discapacidad');
            $table->string('codigo_doc_extranjero', 100)->nullable()->after('tipo_doc_extranjero');
            $table->json('meses_trabajados')->nullable()->after('laboral_recibio_pago');
            $table->string('transporte_otro', 150)->nullable()->after('transporte_tiempo');
            $table->string('centro_acogida', 200)->nullable()->after('convive_con');
        });

        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->string('cargo_actual', 150)->nullable()->after('ocupacion');
            $table->string('nombre_institucion', 200)->nullable()->after('cargo_actual');
        });
    }

    public function down(): void
    {
        Schema::table('student_rude_records', function (Blueprint $table) {
            $table->dropColumn(['tipo_doc_extranjero', 'codigo_doc_extranjero', 'meses_trabajados', 'transporte_otro', 'centro_acogida']);
        });
        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->dropColumn(['cargo_actual', 'nombre_institucion']);
        });
    }
};
