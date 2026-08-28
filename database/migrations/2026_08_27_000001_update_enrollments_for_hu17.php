<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_years', 'nombre')) {
                $table->string('nombre', 50)->unique()->after('id');
            }
            if (! Schema::hasColumn('academic_years', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('academic_years', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            }
            if (! Schema::hasColumn('academic_years', 'estado')) {
                $table->enum('estado', ['ACTIVA', 'CERRADA', 'PROXIMA'])->default('PROXIMA')->after('fecha_fin');
            }
        });

        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'estudiante_id')) {
                $table->foreignId('estudiante_id')->nullable()->after('id')->constrained('students')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'gestion_id')) {
                $table->foreignId('gestion_id')->nullable()->after('estudiante_id')->constrained('academic_years')->restrictOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'fecha')) {
                $table->date('fecha')->nullable()->after('gestion_id');
            }
            if (! Schema::hasColumn('enrollments', 'estado')) {
                $table->enum('estado', ['PENDIENTE', 'COMPLETADA', 'ANULADA'])->default('PENDIENTE')->after('fecha');
            }
            if (! Schema::hasColumn('enrollments', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('estado');
            }
        });

        if (! Schema::hasIndex('enrollments', 'enrollments_gestion_id_estado_index')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->index(['gestion_id', 'estado']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'estudiante_id')) {
                try {
                    $table->dropForeign(['estudiante_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('estudiante_id');
            }
            if (Schema::hasColumn('enrollments', 'gestion_id')) {
                try {
                    $table->dropForeign(['gestion_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('gestion_id');
            }
            foreach (['fecha', 'estado', 'observaciones'] as $col) {
                if (Schema::hasColumn('enrollments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('academic_years', function (Blueprint $table) {
            foreach (['nombre', 'fecha_inicio', 'fecha_fin', 'estado'] as $col) {
                if (Schema::hasColumn('academic_years', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
