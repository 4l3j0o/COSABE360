<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retiro del módulo de solicitudes ARCO (/datos). La tabla de
     * consentimientos (data_consents) se conserva porque la Inscripción
     * en Línea registra allí la aceptación del tratamiento de datos.
     */
    public function up(): void
    {
        Schema::dropIfExists('arco_requests');
    }

    public function down(): void
    {
        Schema::create('arco_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('titular');
            $table->enum('tipo', ['ACCESO', 'RECTIFICACION', 'CANCELACION', 'OPOSICION']);
            $table->text('detalle')->nullable();
            $table->enum('estado', ['RECIBIDA', 'EN_PROCESO', 'RESUELTA', 'RECHAZADA'])->default('RECIBIDA');
            $table->date('fecha_solicitud')->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->text('resolucion')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }
};
