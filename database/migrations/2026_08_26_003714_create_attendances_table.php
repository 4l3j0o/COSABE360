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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('fecha');
            $table->enum('estado', ['PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO']);
            $table->text('justificacion')->nullable();
            $table->foreignId('registered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['student_id', 'fecha', 'course_id', 'subject_id'], 'uk_estudiante_fecha_curso_materia');
            $table->index('course_id');
            $table->index('subject_id');
            $table->index('fecha');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
