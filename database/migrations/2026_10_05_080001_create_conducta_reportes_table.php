<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-65: reportes de conducta — faltas LEVE/GRAVE/MUY_GRAVE
     * reportadas por el docente con fecha, hora, materia y descripción.
     */
    public function up(): void
    {
        Schema::create('conducta_reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('tipo_falta', 16);
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->text('descripcion');
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'fecha'], 'conducta_curso_fecha_idx');
            $table->index(['student_id', 'fecha'], 'conducta_estudiante_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conducta_reportes');
    }
};
