<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-82: Conducta deja de clasificar las faltas en Leve/Grave/Muy grave.
     * El reporte pasa a ser una observación narrativa del estudiante.
     */
    public function up(): void
    {
        Schema::table('conducta_reportes', function (Blueprint $table) {
            $table->dropColumn('tipo_falta');
        });

        Schema::table('conducta_reportes', function (Blueprint $table) {
            $table->index(['section_id', 'student_id', 'fecha'], 'conducta_section_student_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::table('conducta_reportes', function (Blueprint $table) {
            $table->dropIndex('conducta_section_student_fecha_idx');
        });

        Schema::table('conducta_reportes', function (Blueprint $table) {
            $table->string('tipo_falta', 16)->nullable()->after('teacher_id');
        });
    }
};
