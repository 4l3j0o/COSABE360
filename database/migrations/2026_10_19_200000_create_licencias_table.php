<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HU-82: expediente de licencia de un estudiante. Guarda el motivo, la
 * evidencia fotográfica, el rango y el comunicado generado automáticamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('motivo')->nullable();
            $table->string('evidencia_path')->nullable();
            $table->foreignId('announcement_id')->nullable()->constrained('announcements')->nullOnDelete();
            $table->enum('estado', ['ACTIVA', 'ANULADA'])->default('ACTIVA');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'fecha_inicio', 'fecha_fin']);
            $table->index('section_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencias');
    }
};
