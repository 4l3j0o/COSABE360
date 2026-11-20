<?php

namespace Database\Seeders;

use App\Models\DiscountType;
use Illuminate\Database\Seeder;

class DiscountTypeSeeder extends Seeder
{
    /**
     * Tipos de descuento / becas de la mensualidad.
     * Códigos únicos por porcentaje (columna `codigo` es UNIQUE).
     */
    public function run(): void
    {
        $tipos = [
            ['codigo' => 'BECAEX100', 'nombre' => 'BECA EXCELENCIA ACADEMICA 100%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 100, 'importe_fijo' => null, 'orden' => 1],
            ['codigo' => 'CREDITO', 'nombre' => 'DESCUENTO POR CREDITO A FAVOR', 'tipo_aplicacion' => 'IMPORTE_MANUAL', 'porcentaje' => null, 'importe_fijo' => null, 'orden' => 2],
            ['codigo' => 'BECA3HER70', 'nombre' => 'BECA TERCER HERMANO 70%', 'tipo_aplicacion' => 'IMPORTE_FIJO', 'porcentaje' => null, 'importe_fijo' => 549.90, 'orden' => 3],
            ['codigo' => 'BECA3HER100', 'nombre' => 'BECA TERCER HERMANO 100%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 100, 'importe_fijo' => null, 'orden' => 4],
            ['codigo' => 'BECA3HER80', 'nombre' => 'BECA TERCER HERMANO 80%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 80, 'importe_fijo' => null, 'orden' => 5],
            ['codigo' => 'BECAFUN60', 'nombre' => 'BECA POR HIJO(A) DE FUNCIONARIO 60%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 60, 'importe_fijo' => null, 'orden' => 6],
            ['codigo' => 'BECAFUN30', 'nombre' => 'BECA POR HIJO(A) DE FUNCIONARIO 30%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 30, 'importe_fijo' => null, 'orden' => 7],
            ['codigo' => 'BECAFUN40', 'nombre' => 'BECA POR HIJO(A) DE FUNCIONARIO 40%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 40, 'importe_fijo' => null, 'orden' => 8],
            ['codigo' => 'BECAFUN55', 'nombre' => 'BECA POR HIJO(A) DE FUNCIONARIO 55%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 55, 'importe_fijo' => null, 'orden' => 9],
            ['codigo' => 'BECAFUN70', 'nombre' => 'BECA POR HIJO(A) DE FUNCIONARIO 70%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 70, 'importe_fijo' => null, 'orden' => 10],
            ['codigo' => 'BECASOCIO100', 'nombre' => 'BECA POR HIJO(A) O NIETO(A) DE SOCIO 100%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 100, 'importe_fijo' => null, 'orden' => 11],
            ['codigo' => 'BECASOCIO80', 'nombre' => 'BECA POR HIJO(A) O NIETO(A) DE SOCIO 80%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 80, 'importe_fijo' => null, 'orden' => 12],
            ['codigo' => 'BECASOCIO60', 'nombre' => 'BECA POR HIJO(A) O NIETO(A) DE SOCIO 60%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 60, 'importe_fijo' => null, 'orden' => 13],
            ['codigo' => 'BECAESP50', 'nombre' => 'BECA ESPECIAL DEL 50%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 50, 'importe_fijo' => null, 'orden' => 14],
            ['codigo' => 'BECAESP70', 'nombre' => 'BECA ESPECIAL DEL 70%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 70, 'importe_fijo' => null, 'orden' => 15],
            ['codigo' => 'BECAESP100', 'nombre' => 'BECA ESPECIAL DEL 100%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 100, 'importe_fijo' => null, 'orden' => 16],
            ['codigo' => 'BECAESP20', 'nombre' => 'BECA ESPECIAL DEL 20%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 20, 'importe_fijo' => null, 'orden' => 17],
            ['codigo' => 'BECAESP80', 'nombre' => 'CASOS ESPECIALES 80%', 'tipo_aplicacion' => 'PORCENTAJE', 'porcentaje' => 80, 'importe_fijo' => null, 'orden' => 18],
        ];

        foreach ($tipos as $tipo) {
            DiscountType::updateOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }

        $this->command->info('Tipos de descuento cargados: '.count($tipos).'.');
    }
}
