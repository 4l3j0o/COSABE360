<?php

namespace Database\Seeders;

use App\Models\EnfermeriaMotivo;
use Illuminate\Database\Seeder;

class EnfermeriaMotivoSeeder extends Seeder
{
    public function run(): void
    {
        $motivos = [
            'Dolor de cabeza',
            'Fiebre',
            'Dolor abdominal',
            'Golpe o traumatismo',
            'Malestar general',
            'Medicación',
            'Control',
            'Otro',
        ];

        foreach ($motivos as $orden => $nombre) {
            EnfermeriaMotivo::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true, 'orden' => $orden + 1]
            );
        }
    }
}
