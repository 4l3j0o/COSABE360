<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Level;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        $inicial    = Level::where('nombre', 'Inicial')->first();
        $primaria   = Level::where('nombre', 'Primaria')->first();
        $secundaria = Level::where('nombre', 'Secundaria')->first();

        $grados = [
            ['level_id' => $inicial->id,    'nombre' => 'Pre-Kinder', 'orden' => 1],
            ['level_id' => $inicial->id,    'nombre' => 'Kinder',     'orden' => 2],
            ['level_id' => $primaria->id,   'nombre' => '1ro Primaria',   'orden' => 3],
            ['level_id' => $primaria->id,   'nombre' => '2do Primaria',   'orden' => 4],
            ['level_id' => $primaria->id,   'nombre' => '3ro Primaria',   'orden' => 5],
            ['level_id' => $primaria->id,   'nombre' => '4to Primaria',   'orden' => 6],
            ['level_id' => $primaria->id,   'nombre' => '5to Primaria',   'orden' => 7],
            ['level_id' => $primaria->id,   'nombre' => '6to Primaria',   'orden' => 8],
            ['level_id' => $secundaria->id, 'nombre' => '1ro Secundaria', 'orden' => 9],
            ['level_id' => $secundaria->id, 'nombre' => '2do Secundaria', 'orden' => 10],
        ];

        foreach ($grados as $grado) {
            Grade::updateOrCreate(
                ['level_id' => $grado['level_id'], 'nombre' => $grado['nombre']],
                $grado
            );
        }
    }
}
