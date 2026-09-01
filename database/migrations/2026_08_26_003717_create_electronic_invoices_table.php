<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nro_factura');
            $table->string('cuf', 100)->unique();
            $table->string('cufd', 100)->nullable();
            $table->string('cuis', 20)->nullable();
            $table->date('fecha_emision');
            $table->time('hora_emision');
            $table->enum('tipo_factura', ['VENTA', 'NOTA_VENTA'])->default('VENTA');
            $table->enum('tipo_emision', ['ONLINE', 'CONTINGENCIA'])->default('ONLINE');
            $table->string('modalidad', 50);
            $table->string('codigo_sucursal', 10)->default('0');
            $table->string('nit_emisor', 20);
            $table->string('razon_social_emisor', 200);
            $table->string('nit_receptor', 20)->nullable();
            $table->string('tipo_doc_receptor', 20)->nullable();
            $table->string('nro_doc_receptor', 20)->nullable();
            $table->string('complemento_receptor', 5)->nullable();
            $table->string('razon_social_receptor', 200)->nullable();
            $table->string('direccion_receptor', 255)->nullable();
            $table->string('email_receptor', 150)->nullable();
            $table->decimal('total_exento', 10, 2)->default(0);
            $table->decimal('total_descuentos', 10, 2)->default(0);
            $table->decimal('total_iva', 10, 2)->default(0);
            $table->decimal('total_it', 10, 2)->default(0);
            $table->decimal('total_factura', 10, 2);
            $table->string('total_factura_texto', 255)->nullable();
            $table->enum('estado', ['ENVIADA', 'AUTORIZADA', 'RECHAZADA', 'CONTINGENCIA', 'ANULADA'])->default('ENVIADA');
            $table->text('xml_enviado')->nullable();
            $table->text('xml_respuesta')->nullable();
            $table->string('leyenda1', 255)->nullable();
            $table->string('leyenda2', 255)->nullable();
            $table->string('leyenda3', 255)->nullable();
            $table->timestamps();

            $table->index('nro_factura');
            $table->index('cuf');
            $table->index('fecha_emision');
            $table->index('estado');
            $table->index('nit_receptor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_invoices');
    }
};
