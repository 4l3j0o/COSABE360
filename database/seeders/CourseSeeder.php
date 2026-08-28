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
            foreach ($grades as $grade) {
                foreach ($sections->where('grade_id', $grade->id) as $index => $section) {
                    $exists = DB::table('courses')->where([
                        'grade_id' => $grade->id,
                        'section_id' => $section->id,
                        'gestion_id' => $year->id,
                    ])->exists();

                    if (!$exists) {
                        DB::table('courses')->insert([
                            'grade_id' => $grade->id,
                            'section_id' => $section->id,
                            'gestion_id' => $year->id,
                            'nombre' => $grade->nombre . ' "' . $section->nombre . '" - ' . $year->nombre,
                            'aula' => (string) (10 + $index),
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
