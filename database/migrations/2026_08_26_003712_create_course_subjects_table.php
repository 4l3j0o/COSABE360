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
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['subject_id', 'course_id', 'teacher_id'], 'uk_course_subject_docente');
            $table->index('course_id', 'idx_curso');
            $table->index('teacher_id', 'idx_docente');
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
