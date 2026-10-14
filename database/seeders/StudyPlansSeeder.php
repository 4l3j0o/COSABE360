<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\StudyPlan;
use Illuminate\Database\Seeder;

/**
 * Los 8 planes de estudio oficiales con su tipo de evaluación.
 * Idempotente (actualiza por código).
 */
class StudyPlansSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            ['NIFC', 'Inicial en familia Comunitaria', '11: 11:TRIM 3EVAL CUALI', 'Inicial en Familia Comunitaria'],
            ['PVCP1', 'Primaria Vocacional Comunitaria P1', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Primaria Comunitaria Vocacional'],
            ['PVCP2-P4', 'Primaria Vocacional Comunitaria P2 - P4', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Primaria Comunitaria Vocacional'],
            ['PCPP5', 'Primaria Vocacional Comunitaria P5', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Primaria Comunitaria Vocacional'],
            ['PCPP6', 'Primaria Vocacional Comunitaria P6', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Primaria Comunitaria Vocacional'],
            ['SCPS1-2', 'Secundaria Comunitaria Productiva S1- S2', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Secundaria Comunitaria Productiva'],
            ['SCPS3-5', 'Secundaria Comunitaria Productiva S3 - S5', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Secundaria Comunitaria Productiva'],
            ['SCPS6ABC', 'Secundaria Comunitaria Productiva S6ABC', '19: 19:PERIODO/EVALUACION/DIMENSION/OBJETIVO CUANTI', 'Secundaria Comunitaria Productiva'],
        ];

        $n = 0;
        foreach ($planes as [$codigo, $nombre, $tipo, $nivelNombre]) {
            $nivel = Level::where('nombre', $nivelNombre)->first();
            if (! $nivel) {
                $this->command?->warn("Nivel inexistente: {$nivelNombre}, se omite plan {$codigo}.");
                continue;
            }
            StudyPlan::updateOrCreate(
                ['codigo' => $codigo],
                ['nombre' => $nombre, 'tipo_evaluacion' => $tipo, 'level_id' => $nivel->id, 'estado' => 'ACTIVO']
            );
            $n++;
        }

        $this->command?->info("Planes de estudio: {$n}/8.");
    }
}
