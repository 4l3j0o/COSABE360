<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed reproducible de la BD completa (entornos privados con datos reales).
     *
     * Orden:
     *  1. Base del sistema (roles, admin, configuración tributaria).
     *  2. Estructura académica oficial (niveles, grados, paralelos, cursos).
     *  3. Materias, docentes y asignaciones curso-materia-docente (docentes 2026).
     *  4. Tipos de descuento / becas de la mensualidad.
     *  5. Datos reales de estudiantes, tutores, matrículas y RUDE (PII).
     *
     * Para un entorno demo SIN datos personales, comentar EstudiantesRealesSeeder.
     */
    public function run(): void
    {
        $this->call([
            // Base del sistema
            RoleSeeder::class,
            AdminUserSeeder::class,
            TaxConfigurationSeeder::class,

            // Estructura académica oficial
            AcademicYearSeeder::class,
            EstructuraAcademicaSeeder::class,

            // Materias, docentes y asignaciones 2026
            Docentes2026Seeder::class,

            // Descuentos / becas de la mensualidad
            DiscountTypeSeeder::class,

            // Datos reales de estudiantes 2026 (PII — solo entornos privados)
            // Incluye estudiantes, tutores, matrículas y registros RUDE reales.
            EstudiantesRealesSeeder::class,

            // Facturación SFE — catálogo SIN
            EducationalServiceSeeder::class,
        ]);
    }
}
