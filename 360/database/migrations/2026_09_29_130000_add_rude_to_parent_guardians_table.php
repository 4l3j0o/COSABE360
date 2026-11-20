<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: campos oficiales RUDE sección V para padre/madre/tutor.
     */
    public function up(): void
    {
        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->string('apellido_paterno', 150)->nullable()->after('apellidos');
            $table->string('apellido_materno', 150)->nullable()->after('apellido_paterno');
            $table->date('fecha_nacimiento')->nullable()->after('apellido_materno');
            $table->string('idioma', 100)->nullable()->after('fecha_nacimiento');
            $table->string('grado_instruccion', 100)->nullable()->after('idioma');
        });
    }

    public function down(): void
    {
        Schema::table('parent_guardians', function (Blueprint $table) {
            $table->dropColumn(['apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'idioma', 'grado_instruccion']);
        });
    }
};
