<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('academic_years')->where('nombre', '2026')->exists();

        if (! $exists) {
            DB::table('academic_years')->insert([
                'nombre' => '2026',
                'fecha_inicio' => '2026-02-02',
                'fecha_fin' => '2026-11-28',
                'estado' => 'ACTIVA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
