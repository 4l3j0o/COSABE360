<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-54: Educación inclusiva (Ley 070 Arts. 25-27).
     * Registro de adaptación curricular y evaluación diferenciada por estudiante.
     */
    public function up(): void
    {
        Schema::create('student_curricular_adaptations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('condicion', ['DISCAPACIDAD', 'DIFICULTAD_APRENDIZAJE', 'TALENTO_EXTRAORDINARIO'])->default('DIFICULTAD_APRENDIZAJE');
            $table->string('tipo_discapacidad', 150)->nullable()->comment('Auditiva, Visual, Intelectual, etc.');
            $table->string('grado', 100)->nullable()->comment('Leve, Moderado, Grave, Muy grave');
            $table->text('descripcion')->nullable();
            $table->text('plan_adaptacion')->nullable()->comment('Objetivos, apoyos, modalidad');
            $table->string('modalidad', 100)->nullable()->comment('Directa / Indirecta');
            $table->boolean('evaluacion_diferenciada')->default(false);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['student_id', 'condicion'], 'uk_adaptation_student_condicion');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_curricular_adaptations');
    }
};