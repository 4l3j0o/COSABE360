<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Role;
use App\Models\Student;
use App\Models\TaxConfiguration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EnrollmentRegistrationSeeder extends Seeder
{
    /**
     * Inscribe y matricula a los estudiantes de forma coherente:
     *  - El primer estudiante (Lucía Mamani) queda solo INSCRITO (sin matrícula) para pruebas de flujo.
     *  - Los demás estudiantes destacados van a un curso concreto (destinos explícitos).
     *  - Todos los paralelos activos quedan con 2 estudiantes como mínimo, generando
     *    estudiantes adicionales de ser necesario.
     *  - Cada matrícula genera 10 cuotas mensuales (una pagada al mes 3).
     */
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first()
            ?? AcademicYear::first();

        if (! $gestion) {
            $this->command->error('No existe una gestión académica activa.');
            return;
        }

        $montoMensual = (float) (TaxConfiguration::query()->value('monto_mensualidad') ?? 700.00);

        $destinos = [
            'lucia.mamani@sanber.edu.bo'    => null,                    // solo inscrito, sin matrícula
            'diego.flores@sanber.edu.bo'    => '2do Secundaria A',
            'sofia.garcia@sanber.edu.bo'    => '6to Primaria A',
            'mateo.torres@sanber.edu.bo'    => '1ro Secundaria B',
            'camila.herrera@sanber.edu.bo'  => '2do Secundaria B',
            'santiago.rivera@sanber.edu.bo' => '6to Primaria B',
            'isabella.vargas@sanber.edu.bo' => '1ro Secundaria B',
            'sebastian.sanchez@sanber.edu.bo' => '2do Secundaria B',
            'valentina.mamani@sanber.edu.bo' => '6to Primaria C',
            'daniel.herrera@sanber.edu.bo'  => '1ro Secundaria B',
            'lucia.torres@sanber.edu.bo'    => '6to Primaria B',        // cuenta demo estudiante@
        ];

        foreach ($destinos as $email => $cursoNombre) {
            $student = Student::where('email', $email)->first();
            if (! $student) {
                continue;
            }

            if ($cursoNombre === null) {
                $this->inscripcionSola($student, $gestion);
                $this->command->info("{$student->nombres} {$student->apellidos} quedó solo INSCRITO (sin matrícula).");
                continue;
            }

            $course = $this->coursePara($cursoNombre, $gestion);
            if (! $course) {
                $this->command->warn("No se encontró el curso {$cursoNombre}.");
                continue;
            }

            $this->matricular($student, $course, $gestion, $montoMensual);
        }

        // Cobertura mínima: 2 estudiantes matriculados por paralelo
        foreach (Course::where('estado', 'ACTIVO')->orderBy('id')->get() as $course) {
            $matriculados = Registration::where('course_id', $course->id)
                ->where('estado', 'ACTIVA')
                ->count();

            $faltan = max(0, 2 - $matriculados);
            for ($n = 0; $n < $faltan; $n++) {
                $student = $this->crearEstudianteGenerado($course, $n);
                $this->matricular($student, $course, $gestion, $montoMensual);
            }
        }

        $this->command->info('Inscripciones, matrículas y cuotas generadas para todos los estudiantes.');
    }

    private function coursePara(string $nombreCurso, AcademicYear $gestion): ?Course
    {
        $partes = preg_split('/\s+/', trim($nombreCurso));

        $seccion = (string) array_pop($partes);
        $gradoNombre = implode(' ', $partes);

        return Course::where('academic_year_id', $gestion->id)
            ->whereHas('grade', fn ($q) => $q->where('nombre', $gradoNombre))
            ->whereHas('section', fn ($q) => $q->where('nombre', $seccion))
            ->first();
    }

    private function inscripcionSola(Student $student, AcademicYear $gestion): void
    {
        $enrollment = Enrollment::firstOrNew([
            'student_id'       => $student->id,
            'academic_year_id' => $gestion->id,
        ]);
        $enrollment->fecha        = $gestion->fecha_inicio;
        $enrollment->estado       = 'COMPLETADA';
        $enrollment->observaciones = 'Dejado sin matricular para pruebas de flujo';
        $enrollment->save();
    }

    private function matricular(Student $student, Course $course, AcademicYear $gestion, float $montoMensual): void
    {
        $enrollment = Enrollment::firstOrNew([
            'student_id'       => $student->id,
            'academic_year_id' => $gestion->id,
        ]);
        $enrollment->fecha        = $gestion->fecha_inicio;
        $enrollment->estado       = 'COMPLETADA';
        $enrollment->observaciones = null;
        $enrollment->save();

        $registration = Registration::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'course_id'     => $course->id,
                'fecha'         => $gestion->fecha_inicio,
                'monto_mensual' => $montoMensual,
                'observaciones'     => null,
                'estado'        => 'ACTIVA',
            ]
        );

        $this->syncCuotas($registration->id, $student->id, $gestion, $montoMensual);
    }

    private function crearEstudianteGenerado(Course $course, int $n): Student
    {
        $nombresM = ['Andrés', 'Joaquín', 'Emilio', 'Fabián', 'Gustavo', 'Hugo', 'Iván', 'Nicolás'];
        $nombresF = ['Emma', 'Antonella', 'Priyanka', 'Rafaela', 'Gabriela', 'Ariana', 'Daniela', 'Nicole'];
        $apellidos = ['Paredes Quiroga', 'Villarroel Padilla', 'Siles Rojas', 'Zelaya Condori', 'Camacho Ibarra', 'Ramos Suárez', 'Mendoza Añez', 'Hurtado Loza'];

        $semilla = $course->id * 10 + $n;
        $sexo = $semilla % 2 === 0 ? 'F' : 'M';
        $nombres = $sexo === 'M' ? $nombresM : $nombresF;

        $nivel = $course->grade?->level_id;
        $anioNacimiento = match ($nivel) {
            1 => 2019,  // Inicial
            2 => 2015,  // Primaria
            default => 2012, // Secundaria
        };
        $mes = 1 + ($semilla % 4) + 8;          // abril a diciembre
        $dia = 1 + (($course->id + $n * 3) % 27);

        $numeroDocumento = (string) ($course->id * 1000 + $n);
        $email = 'gen' . $course->id . '.' . ($n + 1) . '@sanber.edu.bo';
        $calles = ['Av. Beni', 'Calle La Madrid', 'Av. Las Américas', 'Calle Bolivar', 'Av. Juan XXIII', 'Calle Suipacha', 'Av. Circunvalación', 'Calle Virginia'];

        $direccion = $calles[$semilla % count($calles)] . ' Nro ' . (100 + $n) . ', Tarija';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nombre'       => $nombres[$semilla % count($nombres)],
                'apellido'     => $apellidos[$semilla % count($apellidos)],
                'password'     => Hash::make('password123'),
                'role_id'      => Role::where('nombre', 'Estudiante')->value('id'),
                'telefono'     => '7' . str_pad((string) (rand(1000000, 9999999)), 7, '0', STR_PAD_LEFT),
                'estado'       => 'ACTIVO',
            ]
        );

        $student = Student::updateOrCreate(
            ['numero_documento' => $numeroDocumento],
            [
                'user_id'           => $user->id,
                'codigo'            => 'EST-GEN-' . $course->id . '-' . ($n + 1),
                'nombres'           => $nombres[$semilla % count($nombres)],
                'apellidos'         => $apellidos[$semilla % count($apellidos)],
                'fecha_nacimiento'  => sprintf('%04d-%02d-%02d', $anioNacimiento, $mes, $dia),
                'lugar_nacimiento'  => 'Tarija',
                'sexo'              => $sexo,
                'tipo_documento'    => 'CI',
                'complemento'       => 'TJ',
                'direccion'         => $direccion,
                'telefono'          => $user->telefono,
                'email'             => $email,
                'idioma'            => 'Castellano',
                'cultura'           => null,
                'discapacidad'      => null,
                'quien_es_tutor'    => 'PADRE',
                'estado'            => 'ACTIVO',
            ]
        );

        $padres = ParentGuardian::orderBy('id')->get();
        if ($padres->isNotEmpty()) {
            $padre = $padres->get($semilla % $padres->count());
            $student->parentGuardians()->syncWithoutDetaching([
                $padre->id => [
                    'tipo_relacion' => 'PADRE',
                    'es_principal'  => true,
                    'convive'       => true,
                ],
            ]);
        }

        return $student;
    }

    private function metodoPara(int $id): string
    {
        $metodos = ['EFECTIVO', 'TRANSFERENCIA', 'TARJETA'];
        return $metodos[($id - 1) % count($metodos)];
    }

    private function syncCuotas(int $registrationId, int $studentId, AcademicYear $gestion, float $montoMensual): void
    {
        $anio = (int) substr($gestion->fecha_inicio, 0, 4);

        $cuotas = [];
        foreach (range(2, 11) as $mes) {
            $cuotas[$mes] = [
                'registration_id' => $registrationId,
                'mes'           => $mes,
                'monto'         => $montoMensual,
                'fecha'         => null,
                'metodo_pago'   => null,
                'estado'        => 'PENDIENTE',
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        // Variedad realista: una cuota (mes 3) pagada
        $cuotas[3]['fecha']      = sprintf('%04d-03-05', $anio);
        $cuotas[3]['metodo_pago'] = $this->metodoPara($studentId);
        $cuotas[3]['estado']     = 'PAGADA';

        MonthlyPayment::where('registration_id', $registrationId)->delete();
        MonthlyPayment::insert(array_values($cuotas));
    }
}