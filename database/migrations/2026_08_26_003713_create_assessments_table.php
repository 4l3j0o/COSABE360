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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('tipo', 50);
            $table->foreignId('materia_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('gestion_id')->constrained('academic_years')->restrictOnDelete();
            $table->decimal('peso', 5, 2)->default(1.00);
            $table->date('fecha')->nullable();
            $table->timestamps();

            $table->index('materia_id');
            $table->index('gestion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
