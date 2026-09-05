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
            $table->foreignId('course_subject_id')->nullable()->constrained('course_subjects')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->restrictOnDelete();
            $table->string('nombre', 150);
            $table->string('tipo', 50);
            $table->unsignedTinyInteger('bimestre')->nullable()->comment('1-4');
            $table->decimal('peso', 5, 2)->default(1.00);
            $table->date('fecha')->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'ELIMINADO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('course_subject_id');
            $table->index('subject_id');
            $table->index('academic_year_id');
            $table->index('bimestre');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
