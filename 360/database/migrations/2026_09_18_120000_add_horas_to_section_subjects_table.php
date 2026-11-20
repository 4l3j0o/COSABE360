<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: carga horaria semanal de la asignación (columna del Excel oficial).
     */
    public function up(): void
    {
        Schema::table('section_subjects', function (Blueprint $table) {
            $table->unsignedTinyInteger('horas_semanales')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('section_subjects', function (Blueprint $table) {
            $table->dropColumn('horas_semanales');
        });
    }
};
