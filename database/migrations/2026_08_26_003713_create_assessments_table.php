<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_subject_id')->constrained('course_subjects')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->enum('tipo', ['EXAMEN', 'TAREA', 'PROYECTO', 'PARTICIPACION', 'TRABAJO_DE_CAMPO', 'OTRO']);
            $table->unsignedTinyInteger('bimestre')->comment('1-4');
            $table->decimal('peso', 5, 2)->default(1.00);
            $table->date('fecha')->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->index('bimestre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
