<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electronic_invoice_id')->constrained('electronic_invoices')->cascadeOnDelete();
            $table->unsignedInteger('nro_registro');
            $table->string('codigo_servicio', 20);
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->string('unidad_medida', 20);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('monto_descuento', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2);
            $table->enum('iva_tipo', ['EXENTO'])->default('EXENTO');
            $table->decimal('iva_tasa', 5, 2)->default(0);
            $table->decimal('iva_monto', 10, 2)->default(0);
            $table->enum('it_tipo', ['EXENTO'])->default('EXENTO');
            $table->decimal('it_tasa', 5, 2)->default(0);
            $table->decimal('it_monto', 10, 2)->default(0);
            $table->string('leyenda', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('electronic_invoice_id');
            $table->index('codigo_servicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
