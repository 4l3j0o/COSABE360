<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role_id' => Role::firstOrCreate(['nombre' => 'Administrador'])->id, 'estado' => 'ACTIVO']);
        $this->actingAs($user);

        return $user;
    }

    private function crearDocumento(): StudentDocument
    {
        $est = Student::create([
            'codigo' => 'EST-DOC-'.fake()->unique()->numerify('####'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'M',
            'tipo_documento' => 'CI',
            'numero_documento' => fake()->unique()->numerify('########'),
            'complemento' => '',
            'estado' => 'ACTIVO',
        ]);

        return StudentDocument::create([
            'student_id' => $est->id,
            'tipo_documento' => 'CERTIFICADO_NACIMIENTO',
            'archivo' => 'expedientes/'.$est->codigo.'.pdf',
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_marca_entregado_y_guarda_fecha_de_entrega(): void
    {
        $this->admin();
        $doc = $this->crearDocumento();

        $response = $this->patch(route('student_documents.updateStatus', $doc), ['estado' => 'ENTREGADO']);

        $response->assertRedirect();
        $doc->refresh();
        $this->assertSame('ENTREGADO', $doc->estado);
        $this->assertNotNull($doc->fecha_entrega);
        $this->assertEqualsWithDelta(now()->timestamp, $doc->fecha_entrega->timestamp, 60);
    }

    public function test_devuelve_a_pendiente_y_limpia_fecha_de_entrega(): void
    {
        $this->admin();
        $doc = $this->crearDocumento();

        $this->patch(route('student_documents.updateStatus', $doc), ['estado' => 'ENTREGADO']);
        $doc->refresh();
        $this->assertNotNull($doc->fecha_entrega);

        $this->patch(route('student_documents.updateStatus', $doc), ['estado' => 'PENDIENTE']);
        $doc->refresh();
        $this->assertSame('PENDIENTE', $doc->estado);
        $this->assertNull($doc->fecha_entrega);
    }

    public function test_no_acepta_estado_invalido(): void
    {
        $this->admin();
        $doc = $this->crearDocumento();

        $this->patch(route('student_documents.updateStatus', $doc), ['estado' => 'NO_VALIDO'])
            ->assertSessionHasErrors('estado');

        $this->assertDatabaseHas('student_documents', ['id' => $doc->id, 'estado' => 'PENDIENTE', 'fecha_entrega' => null]);
    }

    public function test_detalle_del_estudiante_muestra_fecha_de_entrega(): void
    {
        $this->admin();
        $doc = $this->crearDocumento();
        $doc->update(['estado' => 'ENTREGADO', 'fecha_entrega' => now()]);

        $response = $this->get(route('students.show', $doc->student_id));

        $response->assertOk()
            ->assertSee('Fecha de entrega')
            ->assertSee($doc->tipo_documento)
            ->assertSee(now()->format('d/m/Y'));
    }
}