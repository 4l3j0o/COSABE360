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
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->foreignId('gestion_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->unique(['grade_id', 'section_id', 'gestion_id'], 'uk_grado_section_gestion');
            $table->index('gestion_id', 'idx_gestion');
            $table->index('estado', 'idx_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
