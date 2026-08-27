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
        Schema::create('grade_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('nota', 5, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['evaluacion_id', 'estudiante_id'], 'uk_evaluacion_estudiante');
            $table->index('estudiante_id');
            $table->index('evaluacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_scores');
    }
};
