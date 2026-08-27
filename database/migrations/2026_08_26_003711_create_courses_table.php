<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->string('gestion', 9);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('section_id');
            $table->index('gestion');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
