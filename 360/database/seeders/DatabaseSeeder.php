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
     *  3. Catálogo oficial de planes/materias + dictadas por paralelo (PlanesEstudioSeeder).
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
            PermissionSeeder::class,
            AdminUserSeeder::class,
            UsuariosRealesSeeder::class,
            TaxConfigurationSeeder::class,

            // Estructura académica oficial
            AcademicYearSeeder::class,
            EstructuraAcademicaSeeder::class,

            // Bloques del horario de verano por nivel (base para asignar cursos)
            BloquesHorarioSeeder::class,

            // Materias, docentes y asignaciones 2026
            Docentes2026Seeder::class,

            // Cuentas de usuario @cosabe.edu.bo de los docentes (rol Docente)
            DocenteUsuariosSeeder::class,

            // HU-62: nómina oficial (CI/tel/email verdaderos por nombre)
            DocentesDatosOficialesSeeder::class,

            // HU-59: directores de nivel (msuarez Inicial/Primaria, mrodriguez Secundaria)
            DirectorNivelSeeder::class,

            // HU-59: tutores de curso (docente responsable de cada paralelo)
            TutoresCursoSeeder::class,

            // Los 8 planes oficiales con su tipo de evaluación
            StudyPlansSeeder::class,

            // Catálogo oficial de planes de estudio + materias dictadas por paralelo
            PlanesEstudioSeeder::class,

            // Descuentos / becas de la mensualidad
            DiscountTypeSeeder::class,

            // Catálogo base de motivos de enfermería
            EnfermeriaMotivoSeeder::class,

            // Datos reales de estudiantes 2026 (PII – solo entornos privados)
            // Incluye estudiantes, tutores, matrículas y registros RUDE reales.
            EstudiantesRealesSeeder::class,

            // Corrige el paralelo de las matrículas contra Inscripciones-2026.xlsx
            RepararCursosXlsxSeeder::class,

            // Facturación SFE – catálogo SIN
            EducationalServiceSeeder::class,
        ]);
    }
}
