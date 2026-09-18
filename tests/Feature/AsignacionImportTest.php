<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AsignacionImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AsignacionImportTest extends TestCase
{
    use RefreshDatabase;

    private function crearBase(): array
    {
        $nivel = Level::create(['nombre' => 'Primaria Comunitaria Vocacional', 'orden' => 2, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => '1ro Primaria', 'orden' => 1, 'estado' => 'ACTIVO']);
        $seccion = Section::create(['grade_id' => $grado->id, 'nombre' => 'A', 'codigo' => 'T1A', 'capacidad' => 10, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::create(['nombre' => '2026', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']);
        $curso = Course::create(['grade_id' => $grado->id, 'section_id' => $seccion->id, 'academic_year_id' => $gestion->id, 'nombre' => 'T1A', 'estado' => 'ACTIVO']);
        $mat = Subject::create(['nombre' => 'MATEMÁTICA', 'codigo' => '06', 'sigla' => 'MAT', 'level_id' => $nivel->id, 'estado' => 'ACTIVO']);

        $rol = Role::where('nombre', 'Docente')->firstOrCreate(['nombre' => 'Docente']);
        $user = User::create(['nombre' => 'D', 'apellido' => 'D', 'email' => 'd@d.bo', 'password' => bcrypt('x'), 'role_id' => $rol->id, 'estado' => 'ACTIVO']);
        $teacher = Teacher::create(['user_id' => $user->id, 'nombres' => 'JUAN', 'apellidos' => 'PEREZ LOPEZ', 'numero_documento' => '1234567', 'estado' => 'ACTIVO']);

        return compact('curso', 'mat', 'teacher', 'gestion');
    }

    private function xlsx(string $path, array $filas): void
    {
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray(['LISTA DE DOCENTES', '', '', '', ''], null, 'A1');
        $sh->fromArray(['No', 'Curso', 'Materia', 'Docente', 'Carga Horaria'], null, 'A2');
        $r = 3;
        foreach ($filas as $f) {
            $sh->fromArray($f, null, 'A' . $r);
            $r++;
        }
        (new Xlsx($s))->save($path);
    }

    public function test_importa_crea_y_es_idempotente(): void
    {
        $b = $this->crearBase();
        $path = tempnam(sys_get_temp_dir(), 'asig') . '.xlsx';
        $this->xlsx($path, [[1, 'T1A', 'MATEMÁTICA', 'PEREZ LOPEZ JUAN', 4]]);

        $svc = app(AsignacionImportService::class);
        $stats = $svc->importar($path, $b['gestion']->id);
        $this->assertSame(1, $stats['creadas']);

        $cs = CourseSubject::first();
        $this->assertSame($b['curso']->id, $cs->course_id);
        $this->assertSame($b['mat']->id, $cs->subject_id);
        $this->assertSame($b['teacher']->id, $cs->teacher_id);
        $this->assertSame(4, (int) $cs->horas_semanales);

        // Segunda corrida: sin cambios
        $stats = $svc->importar($path, $b['gestion']->id);
        $this->assertSame(0, $stats['creadas']);
        $this->assertSame(1, $stats['existentes']);
        $this->assertSame(1, CourseSubject::count());

        @unlink($path);
    }

    public function test_reporta_no_calzados(): void
    {
        $b = $this->crearBase();
        $path = tempnam(sys_get_temp_dir(), 'asig') . '.xlsx';
        $this->xlsx($path, [
            [1, 'ZZZ', 'MATEMÁTICA', 'PEREZ LOPEZ JUAN', 4],
            [2, 'T1A', 'ASTROLOGÍA', 'PEREZ LOPEZ JUAN', 4],
            [3, 'T1A', 'MATEMÁTICA', 'FANTASMA INEXISTENTE', 4],
        ]);

        $stats = app(AsignacionImportService::class)->importar($path, $b['gestion']->id);
        $this->assertSame(0, $stats['creadas']);
        $this->assertCount(3, $stats['omitidas']);
        $this->assertSame(0, CourseSubject::count());

        @unlink($path);
    }
}
