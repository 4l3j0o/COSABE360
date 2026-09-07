<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los docentes no requieren código interno; se elimina la columna codigo.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'codigo')) {
                $table->dropUnique(['codigo']); // teachers_codigo_unique
                $table->dropIndex('idx_codigo');
                $table->dropColumn('codigo');
            }
        });
    }

    /**
     * Reverse the migrations: re-agrega la columna codigo (nullable).
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('codigo', 20)->nullable()->after('user_id');
        });
    }
};
