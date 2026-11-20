<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bloques del horario de verano por nivel (periodos de clase, recreos y salida).
     * Base para asignar después curso-materia-docente por día.
     */
    public function up(): void
    {
        Schema::create('bloques_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->string('etiqueta', 20); // '1'..'7', 'Recreo', 'Salida'
            $table->enum('tipo', ['CLASE', 'RECREO', 'SALIDA'])->default('CLASE');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->unsignedTinyInteger('orden');
            $table->timestamps();

            $table->unique(['level_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloques_horario');
    }
};
