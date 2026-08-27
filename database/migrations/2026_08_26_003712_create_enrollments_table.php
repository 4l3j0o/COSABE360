<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->string('gestion', 9);
            $table->date('fecha_inscripcion');
            $table->enum('estado', ['ACTIVO', 'RETIRADO', 'TRANSFERIDO'])->default('ACTIVO');
            $table->timestamps();
            $table->unique(['student_id', 'course_id', 'gestion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
