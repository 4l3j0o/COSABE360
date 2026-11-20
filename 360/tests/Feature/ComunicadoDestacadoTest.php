<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComunicadoDestacadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(['RoleSeeder', 'AdminUserSeeder']);
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::where('email', 'admin@sanber.edu.bo')->first();
    }

    private function docente(string $email = 'doc_dest@t.bo'): User
    {
        $rol = Role::where('nombre', 'Docente')->first();
        $user = User::create(['nombre' => 'Doc', 'apellido' => 'Dest', 'email' => $email, 'password' => bcrypt('x'), 'role' => $rol->id, 'estado' => 'ACTIVO']);

        return $user;
    }

    private function publicado(string $titulo, array $extra = []): Announcement
    {
        return Announcement::create(array_merge([
            'titulo' => $titulo, 'contenido' => 'Contenido ' . $titulo,
            'author_id' => $this->admin()->id, 'fecha_publicacion' => now(),
            'estado' => 'PUBLICADO', 'destacado' => false,
        ], $extra));
    }

    private function paraDocentes(Announcement $a): void
    {
        $a->recipients()->create(['tipo_destinatario' => 'DOCENTES']);
    }

    public function test_imagen_se_guarda_en_webp(): void
    {
        $this->actingAs($this->admin());

        $response = $this->post(route('announcements.store'), [
            'titulo' => 'Con imagen', 'contenido' => 'Hola',
            'tipo_destinatario' => 'TODOS', 'estado' => 'PUBLICADO',
            'destacado' => '1',
            'imagen' => UploadedFile::fake()->image('foto.jpg', 800, 600),
        ]);

        $response->assertRedirect(route('announcements.index'));
        $a = Announcement::where('titulo', 'Con imagen')->firstOrFail();
        $this->assertTrue($a->destacado);
        $this->assertStringEndsWith('.webp', $a->imagen_path);
        Storage::disk('local')->assertExists($a->imagen_path);
        $this->assertSame('image/webp', mime_content_type(Storage::disk('local')->path($a->imagen_path)));
    }

    public function test_imagen_mayor_a_2mb_se_rechaza(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('announcements.store'), [
            'titulo' => 'Pesada', 'contenido' => 'Hola',
            'tipo_destinatario' => 'TODOS', 'estado' => 'BORRADOR',
            'imagen' => UploadedFile::fake()->image('grande.jpg')->size(3000),
        ])->assertSessionHasErrors('imagen');

        $this->assertDatabaseMissing('announcements', ['titulo' => 'Pesada']);
    }

    public function test_topbar_muestra_campana_con_sin_leer(): void
    {
        $doc = $this->docente();
        foreach (['Uno' => 40, 'Dos' => 30, 'Tres' => 20, 'Cuatro' => 10] as $t => $min) {
            $a = $this->publicado('Tit ' . $t, ['fecha_publicacion' => now()->subMinutes($min)]);
            $this->paraDocentes($a);
        }
        $dest = $this->publicado('Tit Destacado', ['destacado' => true, 'fecha_publicacion' => now()]);
        $this->paraDocentes($dest);

        $response = $this->actingAs($doc)->get(route('dashboard'));
        $response->assertOk();
        // Sin sección de últimos 3; la campana del topbar trae el conteo (5 sin leer)
        $response->assertDontSee('Últimos comunicados');
        $response->assertSee('mis-comunicados', false);
        $response->assertSee('>5<', false);
        // Pantallazo con carrusel y botón Entendido
        $response->assertSee('modal-destacados', false);
        $response->assertSee('Entendido');
    }

    public function test_campana_baja_al_leer(): void
    {
        $doc = $this->docente();
        $a = $this->publicado('Tit Aviso', ['fecha_publicacion' => now()]);
        $this->paraDocentes($a);

        $this->actingAs($doc)->get(route('dashboard'))->assertSee('>1<', false);
        $this->actingAs($doc)->patch(route('comunicados.bandeja.leido', $a))->assertRedirect();

        $response = $this->actingAs($doc)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('>1<', false);
    }

    public function test_entendido_quita_el_pantallazo(): void
    {
        $doc = $this->docente();
        $dest = $this->publicado('Tit Destacado', ['destacado' => true]);
        $this->paraDocentes($dest);

        $this->actingAs($doc)->get(route('dashboard'))->assertSee('modal-destacados', false);
        $this->actingAs($doc)->patch(route('comunicados.bandeja.leido', $dest))->assertRedirect();

        $this->actingAs($doc)->get(route('dashboard'))->assertDontSee('modal-destacados', false);
    }

    public function test_no_destinatario_no_ve_nada(): void
    {
        $doc = $this->docente();
        $solo = $this->publicado('Solo Estudiantes', ['destacado' => true]);
        $solo->recipients()->create(['tipo_destinatario' => 'ESTUDIANTES']);

        $response = $this->actingAs($doc)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('Solo Estudiantes');
        $response->assertDontSee('modal-destacados', false);
    }

    public function test_borrador_no_sale_en_dashboard(): void
    {
        $doc = $this->docente();
        $b = Announcement::create([
            'titulo' => 'Borrador X', 'contenido' => '...', 'author_id' => $this->admin()->id,
            'estado' => 'BORRADOR', 'destacado' => true,
        ]);
        $this->paraDocentes($b);

        $response = $this->actingAs($doc)->get(route('dashboard'));
        $response->assertDontSee('Borrador X');
        $response->assertDontSee('modal-destacados', false);
    }

    public function test_autor_no_recibe_su_propio_comunicado(): void
    {
        $admin = $this->admin();
        $doc = $this->docente('doc_autor@t.bo');

        $a = $this->publicado('Aviso General Para Todos', ['author_id' => $admin->id]);
        $a->recipients()->create(['tipo_destinatario' => 'TODOS']);

        // El autor NO lo ve en su bandeja de recibidos
        $this->actingAs($admin)->get(route('comunicados.bandeja'))->assertOk()
            ->assertDontSee('Aviso General Para Todos');

        // Los demás sí lo reciben
        $this->actingAs($doc)->get(route('comunicados.bandeja'))->assertOk()
            ->assertSee('Aviso General Para Todos');

        // Y no suma a su contador de sin leer
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertDontSee('Aviso General Para Todos');
    }

    public function test_borrar_vuelve_a_la_pestana_enviados(): void
    {
        $a = $this->publicado('Tit Borrar', ['author_id' => $this->admin()->id]);
        $this->paraDocentes($a);

        $this->actingAs($this->admin())
            ->delete(route('announcements.destroy', $a), ['_f' => ['tab' => 'enviados']])
            ->assertRedirect(route('announcements.index', ['tab' => 'enviados']));

        $this->assertDatabaseMissing('announcements', ['id' => $a->id]);
    }

    public function test_borrar_sin_tab_vuelve_al_index(): void
    {
        $a = $this->publicado('Tit Borrar 2', ['author_id' => $this->admin()->id]);
        $this->paraDocentes($a);

        // Compatibilidad: sin tab se mantiene el comportamiento previo
        $this->actingAs($this->admin())
            ->delete(route('announcements.destroy', $a))
            ->assertRedirect(route('announcements.index'));
    }
}
