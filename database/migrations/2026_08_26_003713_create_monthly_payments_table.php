<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->restrictOnDelete();
            // FK a factura electrónica (diagrama #18 - nullable SET NULL)
            // Nota: sin constrained() aquí para evitar orden de creación; FK se añade en patch posterior si se requiere
            $table->unsignedBigInteger('electronic_invoice_id')->nullable();
            $table->unsignedTinyInteger('mes'); // 1-12
            $table->unsignedSmallInteger('anio')->default((int) date('Y'));
            $table->decimal('monto_esperado', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('monto', 10, 2); // compatibilidad HU-19
            $table->date('fecha_pago')->nullable();
            $table->date('fecha')->nullable(); // compatibilidad
            $table->enum('metodo_pago', ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA', 'OTRO'])->nullable();
            $table->enum('estado', ['PENDIENTE', 'PAGO_PARCIAL', 'PAGADO', 'PAGADA', 'VENCIDO'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'mes', 'anio'], 'uk_registration_month_year');
            $table->index('registration_id');
            $table->index('electronic_invoice_id');
            $table->index('estado');
            $table->index(['anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_payments');
    }
};
