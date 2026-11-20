<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rc_iva_withholdings', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('tipo_sujeto', ['EMPLEADO', 'PROVEEDOR'])->default('EMPLEADO');
            $table->unsignedBigInteger('sujeto_id');
            $table->string('sujeto_nombre', 200);
            $table->string('sujeto_documento', 20);
            $table->string('nro_factura_origen', 50)->nullable();
            $table->decimal('base_imponible', 10, 2);
            $table->decimal('retencion_monto', 10, 2);
            $table->string('comprobante_nro', 50)->nullable();
            $table->enum('estado', ['REGISTRADA', 'CONFIRMADA', 'ANULADA'])->default('REGISTRADA');
            $table->timestamps();

            $table->index('fecha');
            $table->index(['tipo_sujeto', 'sujeto_id']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rc_iva_withholdings');
    }
};
