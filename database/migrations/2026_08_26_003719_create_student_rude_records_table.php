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
        Schema::create('student_rude_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('codigo_rude', 20)->nullable()->unique();
            $table->string('codigo_sie_ue', 20)->nullable();
            $table->enum('turno', ['MANANA', 'TARDE', 'NOCTURNO'])->nullable();
            $table->boolean('transporte')->default(false);
            $table->enum('zona_residencial', ['URBANA', 'RURAL', 'PERIFERIA'])->nullable();
            $table->string('procedencia_ie', 150)->nullable();
            $table->boolean('nuevo_ingreso')->default(true);
            $table->string('idioma_originario', 50)->nullable();
            $table->string('salud_discapacidad', 200)->nullable();
            $table->string('oficialia_nacimiento', 100)->nullable();
            $table->string('libro_nacimiento', 50)->nullable();
            $table->string('partida_nacimiento', 50)->nullable();
            $table->string('folio_nacimiento', 50)->nullable();
            $table->string('anio_nacimiento', 10)->nullable();
            $table->enum('estado', ['PENDIENTE', 'ENVIADO', 'APROBADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->date('fecha_envio')->nullable();
            $table->date('fecha_respuesta')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_rude_records');
    }
};
