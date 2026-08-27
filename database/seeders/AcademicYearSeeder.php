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
                'id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
