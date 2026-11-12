<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermeria_motivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('enfermeria_atenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('nurse_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('motivo_id')->constrained('enfermeria_motivos')->restrictOnDelete();
            $table->dateTime('fecha_hora');
            $table->text('atencion_indicacion');
            $table->text('observaciones_internas')->nullable();
            $table->enum('estado', ['ATENDIDO', 'EN_OBSERVACION', 'DERIVADO', 'ANULADO'])->default('ATENDIDO');
            $table->enum('tipo_derivacion', ['TUTOR_O_MAYOR', 'EMERGENCIA'])->nullable();
            $table->timestamps();

            $table->index(['student_id', 'fecha_hora']);
            $table->index(['fecha_hora', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermeria_atenciones');
        Schema::dropIfExists('enfermeria_motivos');
    }
};
