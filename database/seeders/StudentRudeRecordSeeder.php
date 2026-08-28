<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentRudeRecord;
use Illuminate\Database\Seeder;

class StudentRudeRecordSeeder extends Seeder
{
    /**
     * Genera el registro RUDE (formulario único de registro) para cada estudiante,
     * tomando datos coherenctes con los ya cargados en el estudiante.
     */
    public function run(): void
    {
        $students = Student::orderBy('id')->get();

        foreach ($students as $student) {
            StudentRudeRecord::updateOrCreate(
                ['estudiante_id' => $student->id],
                [
                    'codigo_rude'        => $this->codigoRude($student->id),
                    'codigo_sie_ue'      => 'SIE-0001',
                    'turno'              => 'MANANA',
                    'transporte'         => false,
                    'zona_residencial'   => 'URBANA',
                    'procedencia_ie'     => 'SAN BERNARDO DE TARIJA',
                    'nuevo_ingreso'      => false,
                    'idioma_originario'  => $student->idioma ?: 'Castellano',
                    'salud_discapacidad' => $student->discapacidad,
                    'oficialia_nacimiento' => 'OFICIALÍA ' . $student->lugar_nacimiento,
                    'libro_nacimiento'   => sprintf('L-%04d', $student->id),
                    'partida_nacimiento' => sprintf('P-%04d', $student->id),
                    'folio_nacimiento'   => sprintf('F-%04d', $student->id),
                    'anio_nacimiento'    => substr($student->fecha_nacimiento, 0, 4),
                    'estado'             => $student->id % 3 === 0 ? 'APROBADO' : 'ENVIADO',
                    'fecha_envio'        => '2026-02-10',
                    'fecha_respuesta'    => $student->id % 3 === 0 ? '2026-02-20' : null,
                    'observaciones'      => null,
                ]
            );
        }

        $this->command->info('Registros RUDE generados para todos los estudiantes.');
    }

    private function codigoRude(int $id): string
    {
        return 'RUDE-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }
}
