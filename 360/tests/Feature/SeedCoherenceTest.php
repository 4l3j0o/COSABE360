<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SectionSubject;
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
        // HU-84: los datos reales (PII) ya no se versionan.
        if (! is_file(database_path('data/estudiantes_2026.sql')) || ! is_file(database_path('data/docentes_2026.json'))) {
            $this->markTestSkipped('Datos reales no disponibles: PII fuera del repositorio.');
        }

        $this->seed(DatabaseSeeder::class);

        // 1) Gestión activa 2026
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();
        $this->assertNotNull($gestion, 'Debe existir gestion activa');

        // 2) Estructura académica oficial: Inicial 4 + Primaria 18 + Secundaria 18 = 40
        $sections = Section::where('estado', 'ACTIVO')->get();
        $this->assertGreaterThanOrEqual(40, $sections->count(), 'Estructura oficial incompleta');

        // 3) Docentes oficiales 2026 + asignaciones curso-materia-docente
        $this->assertGreaterThanOrEqual(50, Teacher::count(), 'Docentes 2026 incompletos');
        $this->assertGreaterThan(0, SectionSubject::count(), 'Sin asignaciones curso-materia-docente');

        // 4) Estudiantes reales, matrículas y RUDE
        $this->assertGreaterThan(1000, Student::count(), 'Estudiantes reales no importados');
        $this->assertGreaterThan(0, Registration::count(), 'Sin matrículas');
        $this->assertGreaterThan(0, StudentRudeRecord::count(), 'Sin registros RUDE');

        // 5) Idempotencia: volver a sembrar no duplica datos base
        $nCursos = Section::count();
        $nStudents = Student::count();
        $nTeachers = Teacher::count();
        $nAsignaciones = SectionSubject::count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($nCursos, Section::count());
        $this->assertSame($nStudents, Student::count());
        $this->assertSame($nTeachers, Teacher::count());
        $this->assertSame($nAsignaciones, SectionSubject::count());
    }
}
