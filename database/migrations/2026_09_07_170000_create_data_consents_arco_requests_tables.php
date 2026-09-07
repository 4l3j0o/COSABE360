<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-55: Protección de datos personales y derechos ARCO.
     * - data_consents: consentimiento informado del titular (estudiante/tutor) con finalidad y revocación.
     * - arco_requests: solicitudes de Acceso, Rectificación, Cancelación y Oposición.
     */
    public function up(): void
    {
        Schema::create('data_consents', function (Blueprint $table) {
            $table->id();
            $table->morphs('titular'); // student o parent_guardian
            $table->string('finalidad', 200)->comment('Finalidad del tratamiento de datos');
            $table->boolean('consentimiento')->default(false);
            $table->date('fecha_consentimiento')->nullable();
            $table->date('fecha_revocacion')->nullable();
            $table->string('registrado_por', 150)->nullable();
            $table->timestamps();

            $table->unique(['titular_type', 'titular_id', 'finalidad'], 'uk_consent_titular_finalidad');
        });

        Schema::create('arco_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('titular'); // student o parent_guardian
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

    public function down(): void
    {
        Schema::dropIfExists('arco_requests');
        Schema::dropIfExists('data_consents');
    }
};
