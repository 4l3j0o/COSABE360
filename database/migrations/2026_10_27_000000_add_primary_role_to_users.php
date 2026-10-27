<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'primary_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('primary_role_id')
                    ->nullable()
                    ->after('password')
                    ->constrained('roles')
                    ->nullOnDelete();
            });
        }

        // Toma como principal el primer rol existente en el pivote.
        if (Schema::hasTable('model_has_roles')) {
            DB::statement(
                'UPDATE users u
                 JOIN (
                     SELECT model_id, MIN(role_id) AS role_id
                     FROM model_has_roles
                     WHERE model_type = ?
                     GROUP BY model_id
                 ) r ON r.model_id = u.id
                 SET u.primary_role_id = r.role_id
                 WHERE u.primary_role_id IS NULL',
                [\App\Models\User::class]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'primary_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['primary_role_id']);
                $table->dropColumn('primary_role_id');
            });
        }
    }
};
