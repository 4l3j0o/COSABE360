<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('significant_events', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_evento', 50);
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->text('motivo');
            $table->text('documentos_soporte')->nullable();
            $table->string('cufd_inicio', 100)->nullable();
            $table->enum('estado', ['ABIERTO', 'CERRADO', 'REPORTADO'])->default('ABIERTO');
            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('significant_events');
    }
};
