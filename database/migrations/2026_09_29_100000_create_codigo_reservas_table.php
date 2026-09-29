<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-59: reserva de códigos correlativos. Al abrir el formulario de
     * inscripción se reserva el siguiente código libre (salta reservados
     * vigentes); expira en 30 minutos si la inscripción no se concreta.
     */
    public function up(): void
    {
        Schema::create('codigo_reservas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expira_en');
            $table->enum('estado', ['RESERVADO', 'USADO', 'LIBERADO'])->default('RESERVADO');
            $table->timestamps();

            $table->index(['estado', 'expira_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigo_reservas');
    }
};
