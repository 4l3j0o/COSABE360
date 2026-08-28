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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('docente_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->restrictOnDelete();
            $table->enum('dia', ['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
