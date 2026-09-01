<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Añade FK SET NULL una vez que electronic_invoices ya existe (migr. #31 posterior a #18)
        if (Schema::hasTable('monthly_payments') && Schema::hasTable('electronic_invoices')) {
            Schema::table('monthly_payments', function (Blueprint $table) {
                // Evita duplicar FK si ya existe
                try {
                    $table->foreign('electronic_invoice_id')
                        ->references('id')->on('electronic_invoices')
                        ->nullOnDelete();
                } catch (\Throwable $e) {
                    // Ya existe o motor no soporta en testing sqlite - ignorar
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('monthly_payments')) {
            Schema::table('monthly_payments', function (Blueprint $table) {
                try {
                    $table->dropForeign(['electronic_invoice_id']);
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
