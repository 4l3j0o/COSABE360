<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRudeRecord;
use App\Models\Teacher;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedCoherenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seed_es_coherente(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1) Gestión activa 2026
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();
        $this->assertNotNull($gestion, 'Debe existir gestion activa');

        // 2) Estructura académica oficial: Inicial 4 + Primaria 18 + Secundaria 18 = 40
        $courses = Course::where('estado', 'ACTIVO')->get();
        $this->assertGreaterThanOrEqual(40, $courses->count(), 'Estructura oficial incompleta');

        // 3) Docentes oficiales 2026 + asignaciones curso-materia-docente
        $this->assertGreaterThanOrEqual(50, Teacher::count(), 'Docentes 2026 incompletos');
        $this->assertGreaterThan(0, CourseSubject::count(), 'Sin asignaciones curso-materia-docente');

        // 4) Estudiantes reales, matrículas y RUDE
        $this->assertGreaterThan(1000, Student::count(), 'Estudiantes reales no importados');
        $this->assertGreaterThan(0, Registration::count(), 'Sin matrículas');
        $this->assertGreaterThan(0, StudentRudeRecord::count(), 'Sin registros RUDE');

        // 5) Idempotencia: volver a sembrar no duplica datos base
        $nCursos = Course::count();
        $nStudents = Student::count();
        $nTeachers = Teacher::count();
        $nAsignaciones = CourseSubject::count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($nCursos, Course::count());
        $this->assertSame($nStudents, Student::count());
        $this->assertSame($nTeachers, Teacher::count());
        $this->assertSame($nAsignaciones, CourseSubject::count());
    }
}
