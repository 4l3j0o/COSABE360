<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Database\Seeder;

class StudentDocumentSeeder extends Seeder
{
    /**
     * Genera el expediente documental de cada estudiante (HU-20):
     * certificado de nacimiento, cédula (CI), fotografía, etc.,
     * con estados variados (PENDIENTE / ENTREGADO) para ejercicio realista.
     */
    public function run(): void
    {
        $students = Student::orderBy('id')->get();

        $tipos = [
            'Certificado de Nacimiento',
            'Cédula de Identidad',
            'Fotografía',
            'Certificado de Estudios',
        ];

        foreach ($students as $i => $student) {
            // Cada estudiante recibe los documentos básicos
            foreach ($tipos as $idx => $tipo) {
                $esPendiente = (($i + $idx) % 3 === 0);

                StudentDocument::updateOrCreate(
                    [
                        'student_id'  => $student->id,
                        'tipo_documento' => $tipo,
                    ],
                    [
                        'estado'        => $esPendiente ? 'PENDIENTE' : 'ENTREGADO',
                        'fecha_entrega' => $esPendiente ? null : now()->subDays(rand(1, 60)),
                        'observaciones' => $esPendiente ? 'Pendiente de entrega' : 'Recibido y verificado',
                        'archivo'       => 'documentos/' . $student->id . '/' . str_replace(' ', '_', $tipo) . '.pdf',
                    ]
                );
            }
        }

        $this->command->info('Expedientes documentales generados para los estudiantes.');
    }
}
