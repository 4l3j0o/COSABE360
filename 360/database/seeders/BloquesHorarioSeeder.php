<?php

namespace Database\Seeders;

use App\Models\BloqueHorario;
use App\Models\Level;
use Illuminate\Database\Seeder;

class BloquesHorarioSeeder extends Seeder
{
    /**
     * Horario de VERANO por nivel (periodos de clase, recreos y salida).
     * Base para asignar después curso-materia-docente por día.
     *
     * Formato por fila: [etiqueta, tipo, hora_inicio, hora_fin].
     */
    public function run(): void
    {
        $bloques = [
            // orden 1: Inicial en Familia Comunitaria (8:00-12:00)
            1 => [
                ['1', 'CLASE', '08:00', '08:35'],
                ['2', 'CLASE', '08:35', '09:10'],
                ['Recreo', 'RECREO', '09:10', '09:40'],
                ['3', 'CLASE', '09:40', '10:15'],
                ['4', 'CLASE', '10:15', '10:50'],
                ['Recreo', 'RECREO', '10:50', '11:00'],
                ['5', 'CLASE', '11:00', '11:30'],
                ['6', 'CLASE', '11:30', '12:00'],
            ],
            // orden 2: Primaria Comunitaria Vocacional (7:30-12:20)
            2 => [
                ['1', 'CLASE', '07:30', '08:15'],
                ['2', 'CLASE', '08:15', '09:00'],
                ['Recreo', 'RECREO', '09:00', '09:15'],
                ['3', 'CLASE', '09:15', '09:55'],
                ['4', 'CLASE', '09:55', '10:40'],
                ['Recreo', 'RECREO', '10:40', '10:55'],
                ['5', 'CLASE', '10:55', '11:40'],
                ['6', 'CLASE', '11:40', '12:20'],
            ],
            // orden 3: Secundaria Comunitaria Productiva (14:00-18:50)
            3 => [
                ['1', 'CLASE', '14:00', '14:40'],
                ['2', 'CLASE', '14:40', '15:20'],
                ['Recreo', 'RECREO', '15:20', '15:30'],
                ['3', 'CLASE', '15:30', '16:10'],
                ['4', 'CLASE', '16:10', '16:50'],
                ['Recreo', 'RECREO', '16:50', '17:05'],
                ['5', 'CLASE', '17:05', '17:45'],
                ['6', 'CLASE', '17:45', '18:20'],
                ['Salida', 'SALIDA', '18:20', null],
                ['7', 'CLASE', '18:20', '18:50'],
            ],
        ];

        foreach ($bloques as $ordenNivel => $filas) {
            $level = Level::where('orden', $ordenNivel)->first();
            if (! $level) {
                $this->command->warn("Nivel orden {$ordenNivel} no existe, se omite.");

                continue;
            }

            $orden = 0;
            foreach ($filas as [$etiqueta, $tipo, $inicio, $fin]) {
                $orden++;
                BloqueHorario::updateOrCreate(
                    ['level_id' => $level->id, 'orden' => $orden],
                    [
                        'etiqueta' => $etiqueta,
                        'tipo' => $tipo,
                        'hora_inicio' => $inicio,
                        'hora_fin' => $fin,
                    ]
                );
            }
        }

        $this->command->info('Bloques de horario de verano cargados: '.BloqueHorario::count().'.');
    }
}
