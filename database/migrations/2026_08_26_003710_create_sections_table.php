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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grades')->restrictOnDelete();
            $table->string('nombre', 10);
            $table->foreignId('responsable_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->unsignedTinyInteger('capacidad')->default(40);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->unique(['grado_id', 'nombre'], 'uk_grado_paralelo');
            $table->index('grado_id', 'idx_grado');
            $table->index('responsable_id', 'idx_responsable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
