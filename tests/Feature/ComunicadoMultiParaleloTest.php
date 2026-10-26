<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Role;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * HU-82: comunicados a varios paralelos en un mismo envío.
 */
class ComunicadoMultiParaleloTest extends TestCase
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
            'email' => "m{$this->seq}_".strtolower(str_replace(['/', ' '], '', $rolNombre)).'@test.com',
            'password' => 'password123',
            'role' => $this->rol($rolNombre)->id,
            'estado' => 'ACTIVO',
        ]);
    }

    private function curso(string $nombre = 'Curso'): Section
    {
        $this->seq++;
        $nivel = Level::firstOrCreate(['nombre' => 'Primaria MP'], ['orden' => 1, 'estado' => 'ACTIVO']);
        $grado = Grade::create(['level_id' => $nivel->id, 'nombre' => 'Grado '.$this->seq, 'orden' => 1, 'estado' => 'ACTIVO']);
        $gestion = AcademicYear::firstOrCreate(
            ['nombre' => '2026'],
            ['fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-11-30', 'estado' => 'ACTIVA']
        );

        return Section::create([
            'grade_id' => $grado->id,
            'academic_year_id' => $gestion->id,
            'nombre' => $nombre.' '.$this->seq,
            'codigo' => 'MP'.$this->seq,
            'capacidad' => 40,
            'estado' => 'ACTIVO',
        ]);
    }

    private function docenteDe(Section $curso): User
    {
        $user = $this->usuario('Docente');
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'codigo' => 'DOC-'.$user->id,
            'nombres' => 'Docente',
            'apellidos' => 'MP',
            'numero_documento' => (string) (1000000 + $user->id),
            'estado' => 'ACTIVO',
        ]);
        $materia = Subject::create(['nombre' => 'Mat '.$user->id, 'level_id' => $curso->grade->level_id, 'orden' => 1, 'estado' => 'ACTIVO']);
        SectionSubject::create(['subject_id' => $materia->id, 'section_id' => $curso->id, 'teacher_id' => $teacher->id]);

        return $user;
    }

    public function test_admin_crea_comunicado_a_varios_paralelos(): void
    {
        $admin = $this->usuario('Administrador');
        $a = $this->curso('A');
        $b = $this->curso('B');

        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Aviso a dos paralelos',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_ids' => [$a->id, $b->id],
            'estado' => 'PUBLICADO',
        ]);

        $response->assertRedirect(route('announcements.index'));

        $ann = Announcement::where('titulo', 'Aviso a dos paralelos')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$a->id, $b->id],
            $ann->recipients()->pluck('section_id')->all()
        );
    }

    public function test_docente_de_cualquiera_de_los_paralelos_ve_el_comunicado(): void
    {
        $admin = $this->usuario('Administrador');
        $a = $this->curso('A');
        $b = $this->curso('B');
        $c = $this->curso('C');

        $docA = $this->docenteDe($a);
        $docB = $this->docenteDe($b);
        $docC = $this->docenteDe($c);

        $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Aviso MP visible',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_ids' => [$a->id, $b->id],
            'estado' => 'PUBLICADO',
        ]);

        // El docente del paralelo A (segundo destinatario) también debe verlo.
        $this->assertCount(1, $this->actingAs($docA)->get(route('comunicados.bandeja'))->viewData('announcements'));
        $this->assertCount(1, $this->actingAs($docB)->get(route('comunicados.bandeja'))->viewData('announcements'));
        $this->assertCount(0, $this->actingAs($docC)->get(route('comunicados.bandeja'))->viewData('announcements'));
    }

    public function test_director_de_nivel_no_puede_enviar_a_paralelo_ajeno(): void
    {
        $director = $this->usuario('Director');
        $inicial = Level::firstOrCreate(['nombre' => 'Inicial MP'], ['orden' => 2, 'estado' => 'ACTIVO']);
        $director->nivelesACargo()->attach($inicial->id);

        // Cursos en Primaria (nivel ajeno al Director)
        $a = $this->curso('A');
        $b = $this->curso('B');

        $response = $this->actingAs($director)->post(route('announcements.store'), [
            'titulo' => 'Ajeno',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_ids' => [$a->id, $b->id],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('announcements', ['titulo' => 'Ajeno']);
    }

    public function test_update_reemplaza_destinatarios(): void
    {
        $admin = $this->usuario('Administrador');
        $a = $this->curso('A');
        $b = $this->curso('B');

        $this->actingAs($admin)->post(route('announcements.store'), [
            'titulo' => 'Para editar',
            'contenido' => 'Contenido',
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_ids' => [$a->id, $b->id],
        ]);

        $ann = Announcement::where('titulo', 'Para editar')->firstOrFail();

        $this->actingAs($admin)->put(route('announcements.update', $ann), [
            'tipo_destinatario' => 'CURSO_ESPECIFICO',
            'section_ids' => [$a->id],
        ]);

        $this->assertEquals([$a->id], $ann->fresh()->recipients()->pluck('section_id')->all());
    }
}
