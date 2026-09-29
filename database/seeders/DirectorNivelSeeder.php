<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Seeder;

class DirectorNivelSeeder extends Seeder
{
    /**
     * HU-59: directores de nivel.
     * - msuarez@cosabe.edu.bo -> Inicial + Primaria
     * - mrodriguez@cosabe.edu.bo -> Secundaria
     */
    public function run(): void
    {
        $mapa = [
            'msuarez@cosabe.edu.bo' => ['Inicial en Familia Comunitaria', 'Primaria Comunitaria Vocacional'],
            'mrodriguez@cosabe.edu.bo' => ['Secundaria Comunitaria Productiva'],
        ];

        foreach ($mapa as $email => $niveles) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            $ids = Level::whereIn('nombre', $niveles)->pluck('id')->all();
            if ($ids !== []) {
                $user->nivelesACargo()->sync($ids);
            }
        }
    }
}
