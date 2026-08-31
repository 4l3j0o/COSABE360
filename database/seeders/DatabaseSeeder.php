<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Base del sistema
            RoleSeeder::class,
            AdminUserSeeder::class,
            TaxConfigurationSeeder::class,

            // Estructura académica
            AcademicYearSeeder::class,
            GradoSeeder::class,
            SectionSeeder::class,
            SubjectSeeder::class,

            // Personas
            ParentGuardianSeeder::class,
            StudentSeeder::class,
            TeacherSeeder::class,

            // Cursos y asignación curricular
            CourseSeeder::class,
            CourseSubjectSeeder::class,
            ScheduleSeeder::class,

            // Académico: inscripción y matrícula + cuotas
            EnrollmentRegistrationSeeder::class,

            // Académico: evaluaciones por curso, notas y asistencia
            AssessmentSeeder::class,
            GradeScoreSeeder::class,
            AttendanceSeeder::class,

            // Expediente del estudiante
            StudentRudeRecordSeeder::class,
            MedicalRecordSeeder::class,
            StudentDocumentSeeder::class,

            // Bitácora del sistema
            OperationHistorySeeder::class,

            // Comunicados para distintos destinatarios (roles y curso específico)
            AnnouncementSeeder::class,
        ]);
    }
}