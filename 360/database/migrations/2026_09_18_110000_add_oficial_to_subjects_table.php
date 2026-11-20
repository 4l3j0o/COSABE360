<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: campos oficiales de la materia (listas por nivel y curso).
     * codigo: 01, 01.01... sigla: MAT, LELO... area: campo de saberes...
     * grupo: listas oficiales donde aplica (NIFC, PVCP1, SCPS1-2...).
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('codigo', 10)->nullable()->after('nombre');
            $table->string('sigla', 15)->nullable()->after('codigo');
            $table->string('area', 100)->nullable()->after('sigla');
            $table->string('grupo', 100)->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'sigla', 'area', 'grupo']);
        });
    }
};
