<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->unsignedSmallInteger('horas_semanales')->default(1);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('nombre');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
