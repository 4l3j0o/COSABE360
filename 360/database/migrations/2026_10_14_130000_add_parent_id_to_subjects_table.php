<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jerarquía de materias (padre → subcategorías): ej. CL → LELO+LE,
     * CN → BIO+QUIM+FIS. El padre promedia a sus hijas en el boletín.
     */
    public function up(): void
    {
        if (Schema::hasColumn('subjects', 'parent_id')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('level_id')->constrained('subjects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
