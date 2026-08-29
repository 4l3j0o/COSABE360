<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
$table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->restrictOnDelete();
            $table->date('fecha')->nullable();
            $table->enum('estado', ['PENDIENTE', 'COMPLETADA', 'ANULADA'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
