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

            // HU-59: materias oficiales por nivel y curso (código/sigla/área/grupo)
            MateriasOficialesSeeder::class,

            // HU-59: normaliza duplicadas (fusiona con remapeo, elimina sin uso)
            NormalizarMateriasSeeder::class,

            // HU-59: aplica lista oficial de libretas (fusiones CL/CFIL, TEC S1-2)
            MateriasLibretaSeeder::class,

            // HU-59: asignaciones curso-materia-docente desde Excel oficial 2026
            AsignacionesExcelSeeder::class,

            // HU-59: fusiona asignaciones con nivel cruzado en la fila correcta
            LimpiarCruceNivelSeeder::class,

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
