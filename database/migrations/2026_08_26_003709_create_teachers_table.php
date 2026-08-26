<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('codigo', 20)->unique();
            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->string('especialidad', 150)->nullable();
            $table->enum('tipo_documento', ['CI', 'PASAPORTE', 'OTRO'])->default('CI');
            $table->string('numero_documento', 20);
            $table->string('complemento', 5)->default('');
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 150)->nullable()->unique();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['numero_documento', 'complemento'], 'uk_documento');
            $table->index('codigo');
            $table->index(['nombres', 'apellidos']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
