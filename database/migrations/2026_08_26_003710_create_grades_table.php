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
            $table->foreignId('nivel_id')->constrained('levels')->restrictOnDelete();
            $table->string('nombre', 50);
            $table->unsignedTinyInteger('orden');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['nivel_id', 'nombre'], 'uk_nivel_grado');
            $table->index('nivel_id');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
