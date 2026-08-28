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
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('orden');
        });

        DB::table('levels')->insert([
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
