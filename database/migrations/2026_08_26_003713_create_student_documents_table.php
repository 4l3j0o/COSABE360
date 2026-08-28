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
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('tipo_documento', 50);
            $table->string('archivo', 500);
            $table->enum('estado', ['PENDIENTE', 'ENTREGADO', 'VERIFICADO'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index('student_id', 'idx_estudiante');
            $table->index('tipo_documento', 'idx_tipo');
            $table->index('estado', 'idx_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
