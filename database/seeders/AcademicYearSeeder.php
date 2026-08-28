<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('academic_years')->where('id', 1)->exists();

        if (! $exists) {
            DB::table('academic_years')->insert([
                'id'           => 1,
                'nombre'       => '2026',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin'    => '2026-11-30',
                'estado'       => 'ACTIVA',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
