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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('students')->cascadeOnDelete();
            $table->string('grupo_sanguineo', 5)->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos')->nullable();
            $table->text('condiciones_especiales')->nullable();
            $table->string('contacto_emergencia_nombre', 150)->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->string('contacto_emergencia_parentesco', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
