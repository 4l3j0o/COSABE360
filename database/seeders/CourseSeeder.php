<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $grades = DB::table('grades')->get();
        $sections = DB::table('sections')->get();
        $year = DB::table('academic_years')->where('estado', 'ACTIVA')->first() ?? DB::table('academic_years')->first();

        if ($grades->count() > 0 && $sections->count() > 0 && $year) {
            foreach ($grades->take(3) as $grade) {
                foreach ($sections->take(2) as $section) {
                    $exists = DB::table('courses')->where([
                        'grade_id' => $grade->id,
                        'paralelo_id' => $section->id,
                        'gestion_id' => $year->id,
                    ])->exists();

                    if (!$exists) {
                        DB::table('courses')->insert([
                            'grade_id' => $grade->id,
                            'paralelo_id' => $section->id,
                            'gestion_id' => $year->id,
                            'nombre' => $grade->nombre . ' "' . $section->nombre . '" - ' . $year->nombre,
                            'estado' => 'ACTIVO',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
