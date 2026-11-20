<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contingency_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electronic_invoice_id')->constrained('electronic_invoices')->cascadeOnDelete();
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->unsignedTinyInteger('max_intentos')->default(5);
            $table->enum('estado', ['PENDIENTE', 'ENVIANDO', 'COMPLETADO', 'FALLIDO'])->default('PENDIENTE');
            $table->text('ultimo_error')->nullable();
            $table->timestamp('proximo_intento')->nullable();
            $table->timestamps();

            $table->index('electronic_invoice_id');
            $table->index('estado');
            $table->index('proximo_intento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contingency_queues');
    }
};
