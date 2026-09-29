<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\LimpiarCruceNivelSeeder;
use Database\Seeders\MateriasLibretaSeeder;
use Database\Seeders\MateriasOficialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LimpiarCruceNivelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'estado' => 'ACTIVO']);
        $this->seed(MateriasOficialesSeeder::class);
        $this->seed(MateriasLibretaSeeder::class);
    }

    public function test_fusiona_asignacion_cruzada_y_mueve_asistencia(): void
    {
        $sec = Level::where('nombre', 'Secundaria Comunitaria Productiva')->first();
        $grado = Grade::create(['level_id' => $sec->id, 'nombre' => '5to Secundaria', 'orden' => 5, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'C', 'codigo' => 'S5X', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'S5X', 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'D', 'apellido' => 'D', 'email' => 'd@d.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'D', 'apellidos' => 'D', 'numero_documento' => '1234567', 'estado' => 'ACTIVO']);

        // Fila cruzada (SOC de Primaria en curso de Secundaria) + fila correcta
        $socPrim = Subject::where('level_id', Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id)->where('sigla', 'SOC')->first();
        $socSec = Subject::where('level_id', $sec->id)->where('sigla', 'SOC')->first();
        $cruzada = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $socPrim->id, 'teacher_id' => $teacher->id]);
        $correcta = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $socSec->id, 'teacher_id' => $teacher->id]);

        $est = Student::create(['codigo' => '1', 'nombres' => 'A', 'apellidos' => 'B', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '7654321', 'estado' => 'ACTIVO']);
        Attendance::create(['student_id' => $est->id, 'course_id' => $curso->id, 'subject_id' => $socPrim->id, 'fecha' => '2026-03-01', 'estado' => 'PRESENTE', 'registered_by_user_id' => $user->id]);

        $this->seed(LimpiarCruceNivelSeeder::class);

        // Fusionada en la correcta, asistencia movida
        $this->assertNull(CourseSubject::find($cruzada->id));
        $this->assertNotNull(CourseSubject::find($correcta->id));
        $this->assertSame($socSec->id, Attendance::first()->subject_id);
        $this->assertSame(1, CourseSubject::where('course_id', $curso->id)->where('subject_id', $socSec->id)->count());
    }
}
