<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grades')->restrictOnDelete();
            $table->string('nombre', 50)->nullable();
            $table->string('paralelo', 5)->default('A');
            $table->unsignedSmallInteger('capacidad')->default(40);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('grado_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
