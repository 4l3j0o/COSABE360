<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-82: agrega el alcance 'DOCENTES_CURSO' (docentes de un curso).
     * Necesario en MySQL porque `tipo_destinatario` es un enum.
     */
    public function up(): void
    {
        Schema::table('announcement_recipients', function (Blueprint $table) {
            $table->enum('tipo_destinatario', ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES', 'DOCENTES_CURSO'])
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('announcement_recipients', function (Blueprint $table) {
            $table->enum('tipo_destinatario', ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES'])
                ->change();
        });
    }
};
