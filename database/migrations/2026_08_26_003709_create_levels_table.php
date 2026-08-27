<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->unsignedTinyInteger('orden');
<<<<<<< HEAD
$table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
=======
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
>>>>>>> 0b96e2e4b9af81a3a9822c02b9a9a299352e0404
            $table->timestamps();

            $table->index('orden');
        });

<<<<<<< HEAD
        \DB::table('levels')->insert([
=======
        DB::table('levels')->insert([
>>>>>>> 0b96e2e4b9af81a3a9822c02b9a9a299352e0404
            ['nombre' => 'Inicial', 'descripcion' => 'Educación Inicial (0 a 5 años)', 'orden' => 1],
            ['nombre' => 'Primaria', 'descripcion' => 'Educación Primaria (1ro a 6to)', 'orden' => 2],
            ['nombre' => 'Secundaria', 'descripcion' => 'Educación Secundaria (1ro a 6to)', 'orden' => 3],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};