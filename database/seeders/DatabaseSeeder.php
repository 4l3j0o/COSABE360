<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            TaxConfigurationSeeder::class,
            GradoSeeder::class,
            SectionSeeder::class,
            SubjectSeeder::class,
            ParentGuardianSeeder::class,
            StudentSeeder::class,
            AcademicYearSeeder::class,
        ]);
    }
}