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
        Schema::table('tax_configurations', function (Blueprint $table) {
            $table->decimal('monto_mensualidad', 10, 2)->default(0)->after('leyenda_ley070');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            $table->dropColumn('monto_mensualidad');
        });
    }
};
