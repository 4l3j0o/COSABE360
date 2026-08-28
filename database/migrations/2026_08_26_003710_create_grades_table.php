<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->restrictOnDelete();
            $table->string('nombre', 50);
            $table->unsignedTinyInteger('orden');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['level_id', 'nombre'], 'uk_level_grado');
            $table->index('level_id');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
