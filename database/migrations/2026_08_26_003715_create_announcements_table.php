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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('contenido');
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('fecha_publicacion')->nullable();
            $table->enum('estado', ['BORRADOR', 'PUBLICADO', 'ARCHIVADO'])->default('BORRADOR');
            $table->timestamps();

            $table->index('author_id');
            $table->index('estado');
            $table->index('fecha_publicacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
