<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_cufds', function (Blueprint $table) {
            $table->id();
            $table->string('cufd', 100)->unique();
            $table->string('codigo_control', 50);
            $table->date('fecha_vigencia');
            $table->string('motivo', 200)->nullable();
            $table->enum('estado', ['ACTIVO', 'VENCIDO', 'ANULADO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('fecha_vigencia');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_cufds');
    }
};
