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
use Database\Seeders\MateriasLibretaSeeder;
use Database\Seeders\MateriasOficialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriasLibretaTest extends TestCase
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

    public function test_nombres_oficiales_de_libreta(): void
    {
        $this->seed(MateriasLibretaSeeder::class);
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id;
        $secundaria = Level::where('nombre', 'Secundaria Comunitaria Productiva')->first()->id;

        // Primaria: 9 áreas, comunicación única
        $this->assertSame(9, Subject::where('level_id', $primaria)->whereNotNull('sigla')->count());
        $cl = Subject::where('level_id', $primaria)->where('sigla', 'CL')->first();
        $this->assertSame('COMUNICACIÓN Y LENGUAJES (CASTELLANA, ORIGINARIA Y LENGUA EXTRANJERA)', $cl->nombre);
        $this->assertSame('TÉCNICA TECNOLÓGICA', Subject::where('level_id', $primaria)->where('sigla', 'TEC')->first()->nombre);
        $this->assertSame('VALORES, ESPIRITUALIDAD Y RELIGIONES', Subject::where('level_id', $primaria)->where('sigla', 'VER')->first()->nombre);

        // Secundaria: nombres oficiales y TEC solo S1-2
        $this->assertSame('COMUNICACIÓN Y LENGUAJES: LENGUA CASTELLANA Y ORIGINARIA', Subject::where('level_id', $secundaria)->where('sigla', 'LELO')->first()->nombre);
        $this->assertSame('TÉCNICA TECNOLÓGICA GENERAL', Subject::where('level_id', $secundaria)->where('sigla', 'TEC')->first()->nombre);
        $this->assertSame('SCPS1-2', Subject::where('level_id', $secundaria)->where('sigla', 'TEC')->first()->grupo);
        $this->assertSame('CIENCIAS NATURALES: BIOLOGÍA - GEOGRAFÍA', Subject::where('level_id', $secundaria)->where('sigla', 'BIO')->first()->nombre);
        $this->assertSame('COSMOVISIONES, FILOSOFÍA Y SICOLOGÍA', Subject::where('level_id', $secundaria)->where('sigla', 'CFIL')->first()->nombre);

        // Idempotente
        $this->seed(MateriasLibretaSeeder::class);
        $this->assertSame(9, Subject::where('level_id', $primaria)->whereNotNull('sigla')->count());
    }

    public function test_fusiona_lenguas_en_cl_con_remapeo(): void
    {
        $primaria = Level::where('nombre', 'Primaria Comunitaria Vocacional')->first()->id;

        // Simula estado pre-libreta: LELO/LE separadas con asignaciones y asistencia
        $lelo = Subject::create(['nombre' => 'Lengua española', 'codigo' => '01.01', 'sigla' => 'LELO', 'level_id' => $primaria, 'estado' => 'ACTIVO']);
        $le = Subject::create(['nombre' => 'Lengua extranjera', 'codigo' => '01.02', 'sigla' => 'LE', 'level_id' => $primaria, 'estado' => 'ACTIVO']);

        $grado = Grade::create(['level_id' => $primaria, 'nombre' => 'G', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'C', 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'D', 'apellido' => 'D', 'email' => 'd@d.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'D', 'apellidos' => 'D', 'numero_documento' => '1234567', 'estado' => 'ACTIVO']);
        CourseSubject::create(['course_id' => $curso->id, 'subject_id' => $lelo->id, 'teacher_id' => $teacher->id]);

        $est = Student::create(['codigo' => '1', 'nombres' => 'A', 'apellidos' => 'B', 'fecha_nacimiento' => '2015-01-01', 'sexo' => 'M', 'numero_documento' => '7654321', 'estado' => 'ACTIVO']);
        Attendance::create(['student_id' => $est->id, 'course_id' => $curso->id, 'subject_id' => $le->id, 'fecha' => '2026-03-01', 'estado' => 'PRESENTE', 'registered_by_user_id' => $user->id]);

        $this->seed(MateriasLibretaSeeder::class);

        $cl = Subject::where('level_id', $primaria)->where('sigla', 'CL')->first();
        $this->assertNull(Subject::find($lelo->id));
        $this->assertNull(Subject::find($le->id));
        $this->assertSame($cl->id, CourseSubject::first()->subject_id);
        $this->assertSame($cl->id, Attendance::first()->subject_id);
    }
}
