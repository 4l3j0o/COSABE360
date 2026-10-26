<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role_id')) {
            return;
        }

        // Asegura que cualquier usuario con role_id tenga su fila en el pivote.
        if (Schema::hasTable('model_has_roles')) {
            DB::table('users')
                ->whereNotNull('role_id')
                ->orderBy('id')
                ->chunkById(500, function ($users) {
                    $filas = $users->map(fn ($user) => [
                        'role_id' => $user->role_id,
                        'model_type' => \App\Models\User::class,
                        'model_id' => $user->id,
                    ])->all();

                    DB::table('model_has_roles')->insertOrIgnore($filas);
                });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
        });
    }
};
