<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->enum('tipo_documento', ['CI', 'PASAPORTE', 'OTRO'])->default('CI');
            $table->string('numero_documento', 20);
            $table->string('complemento', 5)->default('');
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('ocupacion', 100)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'ELIMINADO'])->default('ACTIVO');
            $table->timestamps();
            $table->unique(['numero_documento', 'complemento']);
            $table->unique('correo');
            $table->index(['nombres', 'apellidos']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_guardians');
    }
};
