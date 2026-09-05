<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educational_services', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_sin', 20)->unique();
            $table->string('descripcion', 200);
            $table->string('unidad_medida', 20)->default('Unidad (B1)');
            $table->enum('iva_tipo', ['EXENTO'])->default('EXENTO');
            $table->decimal('iva_tasa', 5, 2)->default(0);
            $table->enum('it_tipo', ['EXENTO'])->default('EXENTO');
            $table->decimal('it_tasa', 5, 2)->default(0);
            $table->string('leyenda_factura', 255)->nullable();
            $table->boolean('aplica_exento_educacion')->default(true);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('codigo_sin');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educational_services');
    }
};
