<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->nullable()->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->decimal('nota', 5, 2)->comment('0.00 - 100.00');
            $table->decimal('promedio_ponderado', 5, 2)->nullable()->comment('Auto-calculado al guardar');
            $table->text('observaciones')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrado_en')->nullable();
            $table->timestamps();

            $table->unique(['evaluacion_id', 'estudiante_id'], 'uk_evaluacion_estudiante');
            $table->unique(['assessment_id', 'student_id'], 'uk_assessment_student');
            $table->index('evaluacion_id');
            $table->index('assessment_id');
            $table->index('estudiante_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scores');
    }
};
