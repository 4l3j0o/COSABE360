<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consentimiento informado del titular (estudiante/tutor) con finalidad
     * y revocación. Usado por la Inscripción en Línea al finalizar.
     * (Las solicitudes ARCO se retiraron del sistema.)
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
    }

    public function down(): void
    {
        Schema::dropIfExists('data_consents');
    }
};
