<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que levels tenga el esquema del Anexo A si aún es stub (HU-11 no mergeado)
        if (Schema::hasTable('levels') && !Schema::hasColumn('levels', 'nombre')) {
            Schema::table('levels', function (Blueprint $table) {
                $table->string('nombre', 50)->unique()->after('id');
                $table->string('descripcion', 255)->nullable()->after('nombre');
                $table->unsignedTinyInteger('orden')->after('descripcion');
                $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO')->after('orden');
                $table->index('orden');
            });
            if (DB::table('levels')->count() === 0) {
                DB::table('levels')->insert([
                    ['nombre' => 'Inicial', 'descripcion' => 'Educación Inicial (0 a 5 años)', 'orden' => 1],
                    ['nombre' => 'Primaria', 'descripcion' => 'Educación Primaria (1ro a 6to)', 'orden' => 2],
                    ['nombre' => 'Secundaria', 'descripcion' => 'Educación Secundaria (1ro a 6to)', 'orden' => 3],
                ]);
            }
        }

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->foreignId('nivel_id')->constrained('levels')->restrictOnDelete();
            $table->unsignedTinyInteger('horas_semanales')->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->index('nivel_id', 'idx_nivel');
            $table->index('estado', 'idx_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
