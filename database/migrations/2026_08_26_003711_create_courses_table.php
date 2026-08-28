<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grades')->restrictOnDelete();
            $table->foreignId('paralelo_id')->constrained('sections')->restrictOnDelete();
            $table->foreignId('gestion_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['grado_id', 'paralelo_id', 'gestion_id'], 'uk_grado_paralelo_gestion');
            $table->index('gestion_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
