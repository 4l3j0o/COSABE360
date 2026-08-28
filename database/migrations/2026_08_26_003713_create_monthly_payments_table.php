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
        Schema::create('monthly_payments', function (Blueprint $table) {
            $table->id();
            // Vínculo con la inscripción (y en últimas con el estudiante)
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            // Mes correspondiente de la cuota (1-12)
            $table->unsignedTinyInteger('mes');
            // CA-19-01: monto de la cuota
            $table->decimal('monto', 10, 2);
            // CA-19-01: fecha de pago (null mientras esté pendiente)
            $table->date('fecha')->nullable();
            // CA-19-01: método de pago (EFECTIVO, TRANSFERENCIA, TARJETA, OTRO)
            $table->string('metodo_pago')->nullable();
            // CA-19-03: estado de la mensualidad
            $table->enum('estado', ['PENDIENTE', 'PAGADA'])->default('PENDIENTE');
            $table->timestamps();

            $table->unique(['enrollment_id', 'mes']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_payments');
    }
};
