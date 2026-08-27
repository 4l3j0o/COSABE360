<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $grados = [
            'Pre-Kinder'       => ['A', 'B'],
            'Kinder'           => ['A', 'B'],
            '1ro Primaria'     => ['A', 'B', 'C'],
            '2do Primaria'     => ['A', 'B', 'C'],
            '3ro Primaria'     => ['A', 'B', 'C'],
            '4to Primaria'     => ['A', 'B', 'C'],
            '5to Primaria'     => ['A', 'B', 'C'],
            '6to Primaria'     => ['A', 'B', 'C'],
            '1ro Secundaria'   => ['A', 'B'],
            '2do Secundaria'   => ['A', 'B'],
        ];

        foreach ($grados as $gradoNombre => $paralelos) {
            $grado = Grade::where('nombre', $gradoNombre)->first();

            if (! $grado) {
                continue;
            }

            foreach ($paralelos as $nombre) {
                Section::updateOrCreate(
                    ['grado_id' => $grado->id, 'nombre' => $nombre],
                    [
                        'responsable_id' => null,
                        'capacidad'      => 40,
                        'estado'         => 'ACTIVO',
                    ]
                );
            }
        }
    }
}
