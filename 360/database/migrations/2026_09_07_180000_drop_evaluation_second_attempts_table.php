<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina el módulo de segunda instancia / examen de recuperación (HU-51 retirado).
     */
    public function up(): void
    {
        Schema::dropIfExists('evaluation_second_attempts');
    }

    public function down(): void
    {
        // No se restaura la tabla automáticamente.
    }
};