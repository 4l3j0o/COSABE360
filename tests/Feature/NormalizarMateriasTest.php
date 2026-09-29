<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\MateriasOficialesSeeder;
use Database\Seeders\NormalizarMateriasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizarMateriasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        Level::create(['nombre' => 'Inicial en Familia Comunitaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        Level::create(['nombre' => 'Secundaria Comunitaria Productiva', 'orden' => 3, 'estado' => 'ACTIVO']);
        $this->seed(MateriasOficialesSeeder::class);
    }

    public function test_fusiona_ingles_en_lengua_extranjera_con_remapeo(): void
    {
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id;
        $ingles = Subject::create(['nombre' => 'Inglés', 'level_id' => $primaria, 'estado' => 'ACTIVO']);
        // Libreta: Primaria usa comunicación única -> se fusiona en CL
        $cl = Subject::where('level_id', $primaria)->where('sigla', 'CL')->first();

        $grado = Grade::create(['level_id' => $primaria, 'nombre' => 'G', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'C', 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'D', 'apellido' => 'D', 'email' => 'd@d.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'D', 'apellidos' => 'D', 'numero_documento' => '1234567', 'estado' => 'ACTIVO']);
        $cs = CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $ingles->id, 'teacher_id' => $teacher->id]);

        $est = Student::create(['codigo' => '1', 'nombres' => 'A', 'apellidos' => 'B', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '7654321', 'estado' => 'ACTIVO']);
        Attendance::create(['student_id' => $est->id, 'course_id' => $curso->id, 'subject_id' => $ingles->id, 'fecha' => '2026-03-01', 'estado' => 'PRESENTE', 'registered_by_user_id' => $user->id]);

        $this->seed(NormalizarMateriasSeeder::class);
        $this->seed(NormalizarMateriasSeeder::class);

        // La vieja desaparece, la canónica absorbe todo
        $this->assertNull(Subject::find($ingles->id));
        $this->assertSame($cl->id, $cs->fresh()->subject_id);
        $this->assertSame($cl->id, Attendance::first()->subject_id);
        $this->assertSame(1, CourseSubject::where('subject_id', $cl->id)->count());
    }

    public function test_elimina_antiguas_sin_referencias(): void
    {
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id;
        Subject::create(['nombre' => 'Matemáticas', 'level_id' => $primaria, 'estado' => 'ACTIVO']);

        $this->seed(NormalizarMateriasSeeder::class);

        $this->assertNull(Subject::where('level_id', $primaria)->where('nombre', 'Matemáticas')->first());
        // La canónica sigue intacta
        $this->assertNotNull(Subject::where('level_id', $primaria)->where('sigla', 'MAT')->first());
    }
}
