<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Sprint2SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    }

    public function test_comunicado_rechaza_svg_aunque_sea_imagen(): void
    {
        $admin = User::create([
            'nombre' => 'Admin',
            'apellido' => 'Seguridad',
            'email' => 'admin_sprint2_'.fake()->unique()->numerify('####').'@test.local',
            'password' => Hash::make('password'),
            'role' => Role::where('nombre', 'Administrador')->value('id'),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($admin)
            ->post(route('announcements.store'), [
                'titulo' => 'Prueba SVG',
                'contenido' => 'Contenido de prueba',
                'tipo_destinatario' => 'TODOS',
                'imagen' => UploadedFile::fake()->create('archivo.svg', 10, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors('imagen');
    }
}
