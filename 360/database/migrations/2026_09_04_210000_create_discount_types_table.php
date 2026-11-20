<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipos de descuento / becas de la mensualidad.
     */
    public function up(): void
    {
        Schema::create('discount_types', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->enum('tipo_aplicacion', ['PORCENTAJE', 'IMPORTE_FIJO', 'IMPORTE_MANUAL'])->default('PORCENTAJE');
            $table->decimal('porcentaje', 5, 2)->nullable()->comment('% del importe base a descontar');
            $table->decimal('importe_fijo', 10, 2)->nullable()->comment('Importe fijo a descontar');
            $table->unsignedInteger('orden')->default(1);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_types');
    }
};
