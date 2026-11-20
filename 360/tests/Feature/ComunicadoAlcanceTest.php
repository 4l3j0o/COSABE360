<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * HU-65: alcance de comunicados.
 * - Docente crea solo para sus cursos.
 * - Director de nivel crea solo para sus niveles (docentes del nivel / cursos del nivel).
 * - Bandeja filtra por destinatario y marca leídos (es_nuevo).
 */
class ComunicadoAlcanceTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function rol(string $nombre): Role
    {
        return Role::firstOrCreate(['nombre' => $nombre], ['descripcion' => "Rol {$nombre}"]);
    }

    private function usuario(string $rolNombre): User
    {
        $this->seq++;

        return User::create([
            'nombre' => 'Test',
            'apellido' => "U{$this->seq}",
            'email' => "u{$this->seq}_" . strtolower(str_replace(['/', ' '], '', $rolNombre)) . '@test.com',
            'password' => 'password123',
            'role' => $this->rol($rolNombre)->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function nivel(string $nombre): Level
    {
        return Level::firstOrCreate(
            ['nombre' => $nombre],
            ['orden' => Level::count() + 1, 'estado' => 'ACTIVO']
        );
    }

    private function curso(Level $level, string $sufijo = 'A'): Section
    {
        $this->seq++;
        $grado = Grade::create(['level_id' => $level->id, 'nombre' => "Grado {$this->seq}", 'orden' => 1, 'estado' => 'ACTIVO']);
        $paralelo = Section::create(['grade_id' => $grado->id, 'nombre' => $sufijo, 'capacidad' => 40, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(
            ['nombre' => '2026'],
            ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']
        );

        return Section::create([
            'grade_id' => $grado->id,
            'section_id' => $paralelo->id,
            'academic_year_id' => $gestion->id,
            'nombre' => "Curso {$this->seq} {$sufijo}",
            'estado' => 'ACTIVO',
        ]);
    }

    private function docenteDe(Section $curso): User
    {
        $user = $this->usuario('Docente');
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'codigo' => 'DOC-' . $user->id . '-' . rand(100, 999),
            'nombres' => 'Docente',
            'apellidos' => 'Prueba',
            'numero_documento' => (string) rand(1000000, 9999999),
            'estado' => 'ACTIVO',
        ]);
        $materia = Subject::create(['nombre' => 'Materia ' . $teacher->id . '-' . rand(100, 999), 'level_id' => $curso->grade->level_id, 'orden' => 1, 'estado' => 'ACTIVO']);
        SectionSubject::create(['subject_id' => $materia->id, 'section_id' => $curso->id, 'teacher_id' => $teacher->id]);

        return $user;
    }

    private function estudianteEn(Section $curso): User
    {
        $user = $this->usuario('Estudiante');
        $student = Student::create([
            'user_id' => $user->id,
            'codigo' => 'EST-' . $user->id . '-' . rand(100, 999),
            'nombres' => 'Estudiante',
            'apellidos' => 'Prueba',
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'numero_documento' => (string) rand(1000000, 9999999),
            'complemento' => '',
            'estado' => 'ACTIVO',
        ]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $curso->academic_year_id,
            'fecha' => '2026-03-01',
            'estado' => 'COMPLETADA',
        ]);
        Registration::create([
            'enrollment_id' => $enrollment->id,
            'section_id' => $curso->id,
            'fecha' => '2026-03-05',
            'estado' => 'ACTIVA',
        ]);

        return $user;
    }

    private function directorDeNivel(Level $level): User
    {
        $director = $this->usuario('Director');
        $director->nivelesACargo()->attach($level->id);

        return $director;
    }

    private function comunicado(string $tipo, ?int $sectionId = null, ?int $levelId = null): Announcement
    {
        $admin = $this->usuario('Administrador');
        $ann = Announcement::create([
            'titulo' => "Comunicado {$tipo} " . rand(100, 999),
            'contenido' => 'Contenido de prueba',
            'author_id' => $admin->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
        ]);
        $ann->recipients()->create([
            'tipo_destinatario' => $tipo,
            'section_id' => $sectionId,
            'level_id' => $levelId,
        ]);

        return $ann;
    }

    public function test_docente_crea_para_su_curso(): void
    {
        $curso = $this->curso($this->nivel('Primaria Test 1'));
        $docente = $this->docenteDe($curso);

        $response = $this->actingAs($docente)->post(route('announcements.store'), [
            'titulo' => 'Aviso de mi curso',
            'contenido' => 'Mañana traigan cuaderno',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_id' => $curso->id,
            'estado' => 'PUBLICADO',
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', ['titulo' => 'Aviso de mi curso']);
    }

    public function test_docente_no_crea_para_curso_ajeno(): void
    {
        $mio = $this->curso($this->nivel('Primaria Test 2'));
        $ajeno = $this->curso($this->nivel('Secundaria Test 2'));
        $docente = $this->docenteDe($mio);

        $response = $this->actingAs($docente)->post(route('announcements.store'), [
            'titulo' => 'Aviso ajeno',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_id' => $ajeno->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('announcements', ['titulo' => 'Aviso ajeno']);
    }

    public function test_director_de_nivel_solo_su_nivel(): void
    {
        $inicial = $this->nivel('Inicial Test 3');
        $secundaria = $this->nivel('Secundaria Test 3');
        $director = $this->directorDeNivel($inicial);

        // Nivel ajeno: rechazado
        $response = $this->actingAs($director)->post(route('announcements.store'), [
            'titulo' => 'Aviso secundaria',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'NIVEL_DOCENTES',
            'level_id' => $secundaria->id,
        ]);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('announcements', ['titulo' => 'Aviso secundaria']);

        // Su nivel: permitido
        $response = $this->actingAs($director)->post(route('announcements.store'), [
            'titulo' => 'Aviso inicial',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'NIVEL_DOCENTES',
            'level_id' => $inicial->id,
        ]);
        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', ['titulo' => 'Aviso inicial']);

        // TODOS: rechazado para director de nivel
        $response = $this->actingAs($director)->post(route('announcements.store'), [
            'titulo' => 'Aviso todos',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'TODOS',
        ]);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('announcements', ['titulo' => 'Aviso todos']);
    }

    public function test_bandeja_filtra_por_destinatario(): void
    {
        $this->comunicado('TODOS');
        $this->comunicado('DOCENTES');

        $estudiante = $this->usuario('Estudiante');

        $response = $this->actingAs($estudiante)->get(route('comunicados.bandeja'));
        $response->assertOk();
        $titulos = $response->viewData('announcements')->pluck('titulo')->all();

        $this->assertCount(1, $titulos);
    }

    public function test_bandeja_curso_especifico_solo_miembros(): void
    {
        $curso = $this->curso($this->nivel('Primaria Test 5'));
        $otro = $this->curso($this->nivel('Primaria Test 5b'));
        $this->comunicado('CURSO_ESPECIFICO', $curso->id);

        $alumno = $this->estudianteEn($curso);
        $alumnoOtro = $this->estudianteEn($otro);
        $docente = $this->docenteDe($curso);

        $this->assertCount(1, $this->actingAs($alumno)->get(route('comunicados.bandeja'))->viewData('announcements'));
        $this->assertCount(0, $this->actingAs($alumnoOtro)->get(route('comunicados.bandeja'))->viewData('announcements'));
        $this->assertCount(1, $this->actingAs($docente)->get(route('comunicados.bandeja'))->viewData('announcements'));
    }

    public function test_bandeja_nivel_solo_docentes_del_nivel(): void
    {
        $primaria = $this->nivel('Primaria Test 6');
        $secundaria = $this->nivel('Secundaria Test 6');
        $this->comunicado('NIVEL_DOCENTES', null, $secundaria->id);

        $docSec = $this->docenteDe($this->curso($secundaria));
        $docPrim = $this->docenteDe($this->curso($primaria));

        $this->assertCount(1, $this->actingAs($docSec)->get(route('comunicados.bandeja'))->viewData('announcements'));
        $this->assertCount(0, $this->actingAs($docPrim)->get(route('comunicados.bandeja'))->viewData('announcements'));
    }

    public function test_es_nuevo_y_marcar_leido(): void
    {
        $ann = $this->comunicado('TODOS');
        $estudiante = $this->usuario('Estudiante');

        $primera = $this->actingAs($estudiante)->get(route('comunicados.bandeja'));
        $primera->assertOk();
        $this->assertTrue((bool) $primera->viewData('announcements')->first()->es_nuevo);

        $this->actingAs($estudiante)->patch(route('comunicados.bandeja.leido', $ann))->assertRedirect();
        $this->assertDatabaseHas('announcement_reads', ['announcement_id' => $ann->id, 'user_id' => $estudiante->id]);

        $segunda = $this->actingAs($estudiante)->get(route('comunicados.bandeja'));
        $this->assertFalse((bool) $segunda->viewData('announcements')->first()->es_nuevo);
    }

    public function test_borrador_no_visible_en_bandeja(): void
    {
        $admin = $this->usuario('Administrador');
        $ann = Announcement::create([
            'titulo' => 'Borrador oculto',
            'contenido' => 'c',
            'author_id' => $admin->id,
            'estado' => 'BORRADOR',
        ]);
        $ann->recipients()->create(['tipo_destinatario' => 'TODOS']);

        $estudiante = $this->usuario('Estudiante');
        $this->assertCount(0, $this->actingAs($estudiante)->get(route('comunicados.bandeja'))->viewData('announcements'));
        $this->actingAs($estudiante)->get(route('comunicados.bandeja.show', $ann))->assertNotFound();
    }

    public function test_secretaria_ve_bandeja(): void
    {
        $this->comunicado('TODOS');
        $secretaria = $this->usuario('Secretaría');

        $this->actingAs($secretaria)->get(route('comunicados.bandeja'))->assertOk();
    }

    public function test_modulo_unificado_docente_ve_envios(): void
    {
        $curso = $this->curso($this->nivel('Primaria Test 7'));
        $docente = $this->docenteDe($curso);

        $this->actingAs($docente)->post(route('announcements.store'), [
            'titulo' => 'Aviso unificado',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_id' => $curso->id,
            'estado' => 'PUBLICADO',
        ]);

        $response = $this->actingAs($docente)->get(route('announcements.index', ['tab' => 'enviados']));
        $response->assertOk();
        $response->assertSee('Mis Envíos', false);
        $response->assertSee('Aviso unificado', false);
        // Paralelo por código, no nombre largo
        $response->assertSee($curso->codigo, false);
    }

    public function test_modulo_unificado_estudiante_sin_envios(): void
    {
        $estudiante = $this->usuario('Estudiante');

        $response = $this->actingAs($estudiante)->get(route('announcements.index'));
        $response->assertOk();
        $response->assertSee('Recibidos', false);
        $response->assertDontSee('Mis Envíos', false);
    }

    public function test_regente_crea_para_docentes_y_para_docentes_de_un_curso(): void
    {
        $curso = $this->curso($this->nivel('Primaria Regente 1'));
        $regente = $this->usuario('Regente');

        $this->actingAs($regente)->post(route('announcements.store'), [
            'titulo' => 'Aviso a docentes',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'DOCENTES',
            'estado' => 'PUBLICADO',
        ])->assertRedirect(route('announcements.index'));

        $this->assertDatabaseHas('announcement_recipients', [
            'announcement_id' => Announcement::where('titulo', 'Aviso a docentes')->value('id'),
            'tipo_destinatario' => 'DOCENTES',
        ]);

        $this->actingAs($regente)->post(route('announcements.store'), [
            'titulo' => 'Aviso al curso',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'DOCENTES_CURSO',
            'section_id' => $curso->id,
            'estado' => 'PUBLICADO',
        ])->assertRedirect(route('announcements.index'));

        $this->assertDatabaseHas('announcement_recipients', [
            'announcement_id' => Announcement::where('titulo', 'Aviso al curso')->value('id'),
            'tipo_destinatario' => 'DOCENTES_CURSO',
            'section_id' => $curso->id,
        ]);
    }

    public function test_regente_no_crea_para_todos_estudiantes_curso_ni_nivel(): void
    {
        $curso = $this->curso($this->nivel('Primaria Regente 2'));
        $regente = $this->usuario('Regente');

        $casos = [
            ['tipo' => 'TODOS'],
            ['tipo' => 'ESTUDIANTES'],
            ['tipo' => 'CURSO_ESPECIFICO', 'section_id' => $curso->id],
            ['tipo' => 'NIVEL_DOCENTES', 'level_id' => $curso->grade->level_id],
        ];

        foreach ($casos as $i => $caso) {
            $titulo = 'Aviso no permitido ' . $i;
            $this->actingAs($regente)->post(route('announcements.store'), [
                'titulo' => $titulo,
                'contenido' => 'Contenido',
                'tipo_destinatario' => $caso['tipo'],
                'section_id' => $caso['section_id'] ?? null,
                'level_id' => $caso['level_id'] ?? null,
            ])->assertSessionHas('error');

            $this->assertDatabaseMissing('announcements', ['titulo' => $titulo]);
        }
    }

    public function test_docentes_curso_solo_lo_ve_el_docente_del_curso(): void
    {
        $curso = $this->curso($this->nivel('Primaria Regente 3'));
        $otro = $this->curso($this->nivel('Primaria Regente 3b'));
        $regente = $this->usuario('Regente');

        $this->actingAs($regente)->post(route('announcements.store'), [
            'titulo' => 'Solo docentes del curso',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'DOCENTES_CURSO',
            'section_id' => $curso->id,
            'estado' => 'PUBLICADO',
        ])->assertRedirect();

        $ann = Announcement::where('titulo', 'Solo docentes del curso')->firstOrFail();
        $alcance = app(\App\Services\ComunicadoAlcanceService::class);

        $this->assertTrue($alcance->visiblePara($this->docenteDe($curso), $ann));
        $this->assertFalse($alcance->visiblePara($this->estudianteEn($curso), $ann));
        $this->assertFalse($alcance->visiblePara($this->docenteDe($otro), $ann));
    }

    public function test_regente_solo_gestiona_sus_propios_comunicados(): void
    {
        $admin = $this->usuario('Administrador');
        $regente = $this->usuario('Regente');

        $ann = Announcement::create([
            'titulo' => 'Ajeno al regente',
            'contenido' => 'Contenido',
            'author_id' => $admin->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
        ]);
        $ann->recipients()->create(['tipo_destinatario' => 'DOCENTES']);

        $this->actingAs($regente)
            ->put(route('announcements.update', $ann), ['titulo' => 'Hackeado'])
            ->assertSessionHas('error');
        $this->assertDatabaseHas('announcements', ['id' => $ann->id, 'titulo' => 'Ajeno al regente']);

        $this->actingAs($regente)
            ->delete(route('announcements.destroy', $ann))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('announcements', ['id' => $ann->id]);
    }

    public function test_detalle_de_envio_solo_autor_admin_o_director(): void
    {
        $regente = $this->usuario('Regente');
        $ann = Announcement::create([
            'titulo' => 'Envio del regente',
            'contenido' => 'Contenido',
            'author_id' => $regente->id,
            'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO',
        ]);
        $ann->recipients()->create(['tipo_destinatario' => 'DOCENTES']);

        $this->actingAs($regente)->get(route('announcements.enviado', $ann))->assertOk();
        $this->actingAs($this->usuario('Administrador'))->get(route('announcements.enviado', $ann))->assertOk();
        $this->actingAs($this->usuario('Docente'))->get(route('announcements.enviado', $ann))->assertNotFound();
    }

    public function test_recibidos_lista_enlaza_al_detalle(): void
    {
        $ann = $this->comunicado('TODOS');
        $docente = $this->docenteDe($this->curso($this->nivel('Primaria Regente 4')));

        $this->actingAs($docente)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee(route('comunicados.bandeja.show', $ann), false);

        $this->actingAs($docente)
            ->get(route('comunicados.bandeja.show', $ann))
            ->assertOk()
            ->assertSee($ann->titulo);
    }
}
