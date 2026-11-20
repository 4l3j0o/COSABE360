<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-56: el archivo de un documento del expediente no es obligatorio
     * (un requisito puede estar pendiente/entregado sin archivo adjunto).
     */
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            if (Schema::hasColumn('student_documents', 'archivo')) {
                $table->string('archivo', 500)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->string('archivo', 500)->nullable(false)->change();
        });
    }
};
