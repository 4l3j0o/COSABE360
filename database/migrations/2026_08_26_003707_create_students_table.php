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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('codigo', 20)->unique();
            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->date('fecha_nacimiento');
            $table->string('lugar_nacimiento', 150)->nullable();
            $table->enum('sexo', ['M', 'F']);
            $table->enum('tipo_documento', ['CI', 'PASAPORTE', 'OTRO'])->default('CI');
            $table->string('numero_documento', 20);
            $table->string('complemento', 5)->default('');
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 150)->nullable()->unique();
            $table->string('fotografia', 500)->nullable();
            $table->string('idioma', 50)->default('Castellano');
            $table->string('cultura', 50)->nullable();
            $table->string('discapacidad', 100)->nullable();
            $table->softDeletes();
            $table->enum('quien_es_tutor', ['PADRE', 'MADRE', 'TUTOR'])->default('PADRE');
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'EGRESADO', 'TRASLADADO'])->default('ACTIVO');
            $table->timestamps();
            $table->unique(['numero_documento', 'complemento']);
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
        Schema::dropIfExists('students');
    }
};
