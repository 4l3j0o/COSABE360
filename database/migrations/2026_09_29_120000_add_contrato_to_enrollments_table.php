<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: Inscripción en Línea pasos 4-5 (contrato en línea y finalización).
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('nit_facturacion', 20)->nullable()->after('observaciones');
            $table->string('razon_social_facturacion', 200)->nullable()->after('nit_facturacion');
            $table->timestamp('contrato_aceptado_en')->nullable()->after('razon_social_facturacion');
            $table->timestamp('linea_completada_en')->nullable()->after('contrato_aceptado_en');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['nit_facturacion', 'razon_social_facturacion', 'contrato_aceptado_en', 'linea_completada_en']);
        });
    }
};
