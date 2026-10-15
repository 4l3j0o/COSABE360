<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Destacado = pantallazo al ingresar; imagen_path = foto .webp del comunicado.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'destacado')) {
                $table->boolean('destacado')->default(false)->after('estado');
            }
            if (! Schema::hasColumn('announcements', 'imagen_path')) {
                $table->string('imagen_path', 255)->nullable()->after('destacado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['destacado', 'imagen_path']);
        });
    }
};
