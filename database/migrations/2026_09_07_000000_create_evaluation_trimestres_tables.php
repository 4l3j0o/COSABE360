<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nuevo modelo de evaluación por trimestres y criterios (R.M. 473/2021 / R.M. 001/2022).
     *
     * Sustituye conceptualmente al modelo por bimestres/peso (tablas assessments/grade_scores),
     * que se conserva por compatibilidad. Este esquema es el oficial:
     *  - evaluation_plans:      plan/evaluación de un trimestre por course_subject.
     *  - evaluation_criteria:   los 4 criterios (SE/SA/HA/AU) con su porcentaje.
     *  - evaluation_activities: actividades dentro de SABER/HACER (y valor único en SER/AUTOEV).
     *  - evaluation_activity_grades: nota de cada estudiante por actividad.
     */
    public function up(): void
    {
        // Plan/evaluación de un trimestre por curso-materia-docente
        Schema::create('evaluation_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_subject_id')->constrained('course_subjects')->cascadeOnDelete();
            $table->unsignedTinyInteger('trimestre')->comment('1-3');
            $table->string('nombre', 150)->nullable()->comment('Ej: TRIM 2');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['BORRADOR', 'ACTIVO', 'CERRADO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['course_subject_id', 'trimestre'], 'uk_plan_cs_trimestre');
            $table->index('trimestre');
        });

        // Criterios de cada plan (SE, SA, HA, AU)
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_plan_id')->constrained('evaluation_plans')->cascadeOnDelete();
            $table->string('codigo', 10)->comment('SE, SA, HA, AU');
            $table->string('nombre', 100);
            $table->unsignedTinyInteger('porcentaje')->comment('10/45/40/5');
            $table->enum('tipo', ['VALOR_UNICO', 'ACTIVIDADES'])->default('ACTIVIDADES')
                ->comment('VALOR_UNICO: SER/AUTOEV; ACTIVIDADES: SABER/HACER');
            $table->timestamps();

            $table->unique(['evaluation_plan_id', 'codigo'], 'uk_criteria_plan_codigo');
        });

        // Actividades dentro de un criterio (p. ej. exámenes, investigaciones)
        Schema::create('evaluation_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_criteria_id')->constrained('evaluation_criteria')->cascadeOnDelete();
            $table->string('nombre', 200);
            $table->unsignedInteger('orden')->default(1);
            $table->date('fecha')->nullable();
            $table->timestamps();

            $table->index('evaluation_criteria_id');
        });

        // Nota por estudiante y actividad
        Schema::create('evaluation_activity_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_activity_id')->constrained('evaluation_activities')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('nota', 5, 2)->comment('0 a porcentaje_del_criterio');
            $table->text('observaciones')->nullable();
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['evaluation_activity_id', 'student_id'], 'uk_grade_activity_student');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_activity_grades');
        Schema::dropIfExists('evaluation_activities');
        Schema::dropIfExists('evaluation_criteria');
        Schema::dropIfExists('evaluation_plans');
    }
};
