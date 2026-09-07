<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-51: Segunda instancia / examen de recuperación (R.M. 473/2021).
     * Registra la nota de recuperación de un estudiante en un plan (trimestre) reprobado,
     * y su estado (habilitado / rendido / aprobado / reprobado).
     */
    public function up(): void
    {
        Schema::create('evaluation_second_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_plan_id')->constrained('evaluation_plans')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('nota_original', 5, 2)->nullable()->comment('Nota del trimestre antes de la recuperación');
            $table->decimal('nota_recuperacion', 5, 2)->nullable()->comment('Nota obtenida en la segunda instancia');
            $table->enum('estado', ['HABILITADO', 'RENDIDO', 'APROBADO', 'REPROBADO'])->default('HABILITADO');
            $table->date('fecha')->nullable();
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_plan_id', 'student_id'], 'uk_second_attempt_plan_student');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_second_attempts');
    }
};
