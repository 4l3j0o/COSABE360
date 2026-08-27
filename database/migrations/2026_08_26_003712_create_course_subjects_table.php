<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('curso_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('docente_id')->constrained('teachers')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['materia_id', 'curso_id', 'docente_id'], 'uk_course_subject_docente');
            $table->index('curso_id', 'idx_curso');
            $table->index('docente_id', 'idx_docente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_subjects');
    }
};
