<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: niveles a cargo de cada director (directores de nivel).
     * msuarez -> Inicial + Primaria, mrodriguez -> Secundaria.
     */
    public function up(): void
    {
        Schema::create('director_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_level');
    }
};
