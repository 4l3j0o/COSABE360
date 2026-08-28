<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Genera la ficha médica de cada estudiante, coherente con la información
     * del alumno (discapacidad) y su tutor/padre como contacto de emergencia.
     */
    public function run(): void
    {
        $groups = ['O+', 'A+', 'B+', 'O-', 'AB+'];
        $students = Student::orderBy('id')->get();

        foreach ($students as $student) {
            $tutorParent = $student->parentGuardians()->first()
                ?: ParentGuardian::inRandomOrder()->first();

            $contactoNombre = $tutorParent ? $tutorParent->nombres . ' ' . $tutorParent->apellidos : '—';
            $contactoTel    = $tutorParent ? $tutorParent->telefono : null;
            $contactoParent = $tutorParent ? ($student->quien_es_tutor ?: 'PADRE') : 'PADRE';

            MedicalRecord::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'grupo_sanguineo'              => $groups[($student->id - 1) % count($groups)],
                    'alergias'                     => $student->id % 4 === 0 ? 'Polen, Penicilina' : null,
                    'medicamentos'                 => $student->id % 5 === 0 ? 'Rinitis: Loratadina' : null,
                    'condiciones_especiales'       => $student->discapacidad,
                    'contacto_emergencia_nombre'   => $contactoNombre,
                    'contacto_emergencia_telefono' => $contactoTel,
                    'contacto_emergencia_parentesco' => $contactoParent,
                ]
            );
        }

        $this->command->info('Fichas médicas generadas para todos los estudiantes.');
    }
}
