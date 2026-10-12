<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * HU-67: el tutor deja de ser un rol con cuenta. Pasa a ser solo información
     * (registro en `parent_guardians` ligado al estudiante vía `student_parent`).
     *
     * Limpieza: desvincula y elimina las cuentas que hoy tienen el rol Padre/Tutor
     * y borra el rol. La columna `parent_guardians.user_id` queda sin uso (no se
     * elimina porque el drop de un FK no es fiable en SQLite).
     */
    public function up(): void
    {
        $roleId = DB::table('roles')->where('nombre', 'Padre/Tutor')->value('id');

        // Desvincular tutores de sus cuentas antes de borrar usuarios
        DB::table('parent_guardians')->update(['user_id' => null]);

        if ($roleId) {
            // users.role_id es restrictOnDelete: primero los usuarios, luego el rol
            DB::table('users')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }

    public function down(): void
    {
        // Irreversible: el rol y sus cuentas no se recrean automáticamente.
    }
};
