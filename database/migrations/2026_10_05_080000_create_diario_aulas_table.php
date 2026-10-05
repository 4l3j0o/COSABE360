<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-65: diario de aula — bitácora de lo avanzado en cada clase
     * (avance curricular, ejercicios propuestos, observaciones).
     */
    public function up(): void
    {
        Schema::create('diario_aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->date('fecha');
            $table->text('avance_curricular');
            $table->text('ejercicios')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'subject_id', 'fecha'], 'diario_curso_materia_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diario_aulas');
    }
};
