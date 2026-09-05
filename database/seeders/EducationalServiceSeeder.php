<?php

namespace Database\Seeders;

use App\Models\EducationalService;
use Illuminate\Database\Seeder;

class EducationalServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['codigo_sin' => '41010', 'descripcion' => 'SERVICIO EDUCATIVO - INSCRIPCIÓN', 'unidad_medida' => 'Unidad (B1)', 'leyenda_factura' => 'EXENTO IVA - LEY 843 ART 13'],
            ['codigo_sin' => '41020', 'descripcion' => 'SERVICIO EDUCATIVO - MATRÍCULA', 'unidad_medida' => 'Unidad (B1)', 'leyenda_factura' => 'EXENTO IVA - LEY 843 ART 13'],
            ['codigo_sin' => '41030', 'descripcion' => 'SERVICIO EDUCATIVO - MENSUALIDAD', 'unidad_medida' => 'Unidad (B1)', 'leyenda_factura' => 'EXENTO IVA - LEY 843 ART 13'],
            ['codigo_sin' => '41040', 'descripcion' => 'SERVICIO EDUCATIVO - TRANSPORTE ESCOLAR', 'unidad_medida' => 'Unidad (B1)', 'leyenda_factura' => 'EXENTO IVA - LEY 843 ART 13'],
            ['codigo_sin' => '41050', 'descripcion' => 'SERVICIO EDUCATIVO - MATERIAL ESCOLAR', 'unidad_medida' => 'Unidad (B1)', 'leyenda_factura' => 'EXENTO IVA - LEY 843 ART 13'],
        ];

        foreach ($services as $s) {
            EducationalService::updateOrCreate(
                ['codigo_sin' => $s['codigo_sin']],
                $s + [
                    'iva_tipo' => 'EXENTO',
                    'iva_tasa' => 0,
                    'it_tipo' => 'EXENTO',
                    'it_tasa' => 0,
                    'aplica_exento_educacion' => true,
                    'estado' => 'ACTIVO',
                ]
            );
        }
    }
}
