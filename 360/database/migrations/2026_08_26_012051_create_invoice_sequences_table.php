<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_sucursal', 10)->default('0');
            $table->string('punto_venta', 10)->default('0');
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['codigo_sucursal', 'punto_venta'], 'uk_branch_pos');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
