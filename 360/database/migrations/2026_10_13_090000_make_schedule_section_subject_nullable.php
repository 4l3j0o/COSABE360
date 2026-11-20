<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-70: el horario también marca ocupación (docente + día + hora) sin
     * paralelo/materia asignados todavía. section_id y subject_id pasan a
     * opcionales. Las acciones "mi clase actual" solo consideran bloques con
     * paralelo asignado.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['subject_id']);
        });

        DB::statement('ALTER TABLE `schedules` MODIFY `section_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `schedules` MODIFY `subject_id` BIGINT UNSIGNED NULL');

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('schedules')->whereNull('section_id')->orWhereNull('subject_id')->delete();

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['subject_id']);
        });

        DB::statement('ALTER TABLE `schedules` MODIFY `section_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `schedules` MODIFY `subject_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->restrictOnDelete();
        });
    }
};
