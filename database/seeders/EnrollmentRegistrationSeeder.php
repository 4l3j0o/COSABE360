<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\Registration;
use App\Models\Student;
use App\Models\TaxConfiguration;
use Illuminate\Database\Seeder;

class EnrollmentRegistrationSeeder extends Seeder
{
    /**
     * Inscribe y matricula a todos los estudiantes de forma coherente,
     * generando horario de cuotas (10 meses) para cada matrícula.
     *
     * La edad del estudiante (año de nacimiento) determina el grado:
     *  - 2015 -> 6to Primaria
     *  - 2014 -> 1ro Secundaria
     *  - 2013 -> 2do Secundaria
     */
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::first();

        if (! $gestion) {
            $this->command->error('No existe una gestión académica activa.');
            return;
        }

        $montoMensual = (float) TaxConfiguration::query()->value('monto_mensualidad') ?? 700.00;
        $matriculationCost = 1000.00;

        // Mapeo año-nacimiento -> nombre de grado
        $gradoPorAnio = [
            2015 => '6to Primaria',
            2014 => '1ro Secundaria',
            2013 => '2do Secundaria',
        ];

        $students = Student::orderBy('id')->get();

        foreach ($students as $index => $student) {
            // Dejamos al primer estudiante solo como "INSCRITO" (sin matricular) para pruebas
            if ($index === 0) {
                $enrollment = Enrollment::firstOrNew([
                    'student_id' => $student->id,
                    'academic_year_id'    => $gestion->id,
                ]);
                $enrollment->fecha        = $gestion->fecha_inicio;
                $enrollment->estado       = 'COMPLETADA';
                $enrollment->observaciones = 'Dejado sin matricular para pruebas de flujo';
                $enrollment->save();
                continue;
            }

            $anio = (int) substr($student->fecha_nacimiento, 0, 4);
            $gradoNombre = $gradoPorAnio[$anio] ?? '1ro Secundaria';

            $course = Course::whereHas('grade', fn ($q) => $q->where('nombre', $gradoNombre))
                ->where('academic_year_id', $gestion->id)
                ->orderBy('id')
                ->first();

            if (! $course) {
                $this->command->warn("No se encontró curso para {$student->nombres} {$student->apellidos} ({$gradoNombre}).");
                continue;
            }

            // 1) Inscripción (enrollment)
            $enrollment = Enrollment::firstOrNew([
                'student_id' => $student->id,
                'academic_year_id'    => $gestion->id,
            ]);
            $enrollment->fecha        = $gestion->fecha_inicio;
            $enrollment->estado       = 'COMPLETADA';
            $enrollment->observaciones = null;
            $enrollment->save();

            // 2) Matrícula (registration)
            $registration = Registration::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'course_id'          => $course->id,
                    'fecha'              => $gestion->fecha_inicio,
                    'monto_mensual'      => $montoMensual,
                    'observaciones'      => null,
                    'estado'             => 'ACTIVA',
                ]
            );

            // 3) Cuotas mensuales (meses 2..11, 10 cuotas de 700)
            $cuotas = [];
            foreach (range(2, 11) as $mes) {
                $cuotas[$mes] = [
                    'enrollment_id' => $enrollment->id,
                    'mes'           => $mes,
                    'monto'         => $montoMensual,
                    'fecha'         => null,
                    'metodo_pago'   => null,
                    'estado'        => 'PENDIENTE',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
            // Variedad realista: una cuota (en este caso la del mes 3) pagada
            $cuotas[3]['fecha']      = '2026-03-05';
            $cuotas[3]['metodo_pago'] = $this->metodoPara($student->id);
            $cuotas[3]['estado']     = 'PAGADA';

            $this->syncCuotas($enrollment->id, $cuotas);
        }

        $this->command->info('Inscripciones, matrículas y cuotas generadas para todos los estudiantes.');
    }

    private function metodoPara(int $id): string
    {
        $metodos = ['EFECTIVO', 'TRANSFERENCIA', 'TARJETA'];
        return $metodos[($id - 1) % count($metodos)];
    }

    private function syncCuotas(int $enrollmentId, array $cuotas): void
    {
        MonthlyPayment::where('enrollment_id', $enrollmentId)->delete();
        MonthlyPayment::insert(array_values($cuotas));
    }
}
