<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-65: segmentación de comunicados por nivel.
     * Permite destinatario NIVEL_DOCENTES (docentes que dictan en ese nivel).
     */
    public function up(): void
    {
        Schema::table('announcement_recipients', function (Blueprint $table) {
            $table->foreignId('level_id')->nullable()->after('section_id')->constrained('levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('announcement_recipients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('level_id');
        });
    }
};
