<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->date('fecha')->nullable();
            $table->decimal('monto_mensual', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['ACTIVA', 'INACTIVA', 'CANCELADA'])->default('ACTIVA');
            $table->timestamps();

            $table->index('enrollment_id');
            $table->index('course_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
