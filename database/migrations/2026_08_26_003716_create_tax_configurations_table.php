<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('nit_colegio', 20);
            $table->string('razon_social', 200);
            $table->enum('tipo_entidad', ['CON_FINES_LUCRO', 'SIN_FINES_LUCRO'])->default('CON_FINES_LUCRO');
            $table->boolean('exento_iva_educacion')->default(true);
            $table->boolean('exento_it')->default(true);
            $table->boolean('iue_exento')->default(false);
            $table->enum('modalidad_sfe', ['COMPUTARIZADA_EN_LINEA', 'ELECTRONICA_EN_LINEA', 'PORTAL_WEB'])
                ->default('COMPUTARIZADA_EN_LINEA');
            $table->string('cuis', 20)->nullable();
            $table->string('codigo_sucursal', 10)->default('0');
            $table->boolean('agente_retencion_rc_iva')->default(true);
            $table->string('leyenda_iva_exento', 255);
            $table->string('leyenda_it_exento', 255);
            $table->string('leyenda_ley070', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_configurations');
    }
};
