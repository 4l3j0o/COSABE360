<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSubject;
use App\Models\Enrollment;
use App\Models\GradeScore;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SeedCoherenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seed_es_coherente(): void
    {
        $this->seed(DatabaseSeeder::class);

        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();
        $this->assertNotNull($gestion, 'Debe existir gestion activa');

        $courses = Course::where('estado', 'ACTIVO')->get();
        $this->assertTrue($courses->count() >= 24);

        // 1) Todo paralelo tiene >= 2 estudiantes matriculados
        foreach ($courses as $course) {
            $count = Registration::where('course_id', $course->id)
                ->where('estado', 'ACTIVA')
                ->count();
            $this->assertGreaterThanOrEqual(2, $count, "Curso {$course->nombre} con {$count} matriculados");
        }

        // 2) Ningun paralelo sin horario
        foreach ($courses as $course) {
            $this->assertGreaterThan(0, Schedule::where('course_id', $course->id)->count(), "Curso {$course->nombre} sin horario");
        }

        // 3) Aulas unicas y sin docentes cruzados a la misma hora
        $this->assertSame($courses->count(), $courses->pluck('aula')->unique()->count());

        $duplicadosDocente = Schedule::selectRaw('teacher_id, dia, hora_inicio, count(*) as n')
            ->groupBy('teacher_id', 'dia', 'hora_inicio')
            ->havingRaw('count(*) > 1')
            ->get();
        $this->assertTrue($duplicadosDocente->isEmpty(), 'Docentes con cruces de horario');

        $duplicadosCurso = Schedule::selectRaw('course_id, dia, hora_inicio, count(*) as n')
            ->groupBy('course_id', 'dia', 'hora_inicio')
            ->havingRaw('count(*) > 1')
            ->get();
        $this->assertTrue($duplicadosCurso->isEmpty(), 'Curso con dos materias a la misma hora');

        // 4) La fecha de cada evaluacion cae en un dia de clase del curso-materia
        $assessments = Assessment::whereNotNull('course_subject_id')->get();
        $this->assertGreaterThan(0, $assessments->count());

        $nombresDias = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];

        foreach ($assessments as $assessment) {
            $cs = CourseSubject::find($assessment->course_subject_id);
            $this->assertNotNull($cs);

            $dias = Schedule::where('course_id', $cs->course_id)
                ->where('subject_id', $cs->subject_id)
                ->where('teacher_id', $cs->teacher_id)
                ->pluck('dia')
                ->unique();

            $dia = $nombresDias[Carbon::parse($assessment->fecha)->dayOfWeek];

            if ($dias->isNotEmpty()) {
                $this->assertTrue($dias->contains($dia), "{$assessment->nombre} el {$assessment->fecha} es {$dia}, pero el curso solo tiene: {$dias->implode(', ')}");
            }

            $this->assertTrue(Carbon::parse($assessment->fecha)->between(
                Carbon::parse($gestion->fecha_inicio)->startOfDay(),
                Carbon::parse($gestion->fecha_fin)->endOfDay(),
            ), 'Evaluación fuera de la gestión');
        }

        // 5) Notas y asistencia pobladas y coherentes
        $this->assertGreaterThan(0, GradeScore::count());
        $this->assertGreaterThan(0, Attendance::count());

        foreach (Attendance::all() as $att) {
            $this->assertTrue(Carbon::parse($att->fecha)->dayOfWeekIso <= 5, 'Asistencia en fin de semana');
            $this->assertContains($att->estado, ['PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO']);
        }

        // 6) Cuentas demo vinculadas a sus perfiles
        $this->assertNotNull(Teacher::whereHas('usuario', fn ($q) => $q->where('email', 'docente@sanber.edu.bo'))->first(), 'docente@ sin perfil de Teacher');
        $this->assertNotNull(ParentGuardian::whereHas('user', fn ($q) => $q->where('email', 'padre@sanber.edu.bo'))->first(), 'padre@ sin perfil de ParentGuardian');
        $this->assertNotNull(Student::whereHas('user', fn ($q) => $q->where('email', 'estudiante@sanber.edu.bo'))->first(), 'estudiante@ sin perfil de Student');

        $docenteDemo = Teacher::whereHas('usuario', fn ($q) => $q->where('email', 'docente@sanber.edu.bo'))->first();
        $this->assertGreaterThan(0, CourseSubject::where('teacher_id', $docenteDemo->id)->count(), 'docente@ sin materias asignadas');
        $this->assertGreaterThan(0, Schedule::where('teacher_id', $docenteDemo->id)->count(), 'docente@ sin horario');

        // 7) Primer estudiante solo INSCRITO (sin matricula) y el resto con matricula
        $primero = Student::orderBy('id')->first();
        $enrollment = Enrollment::where('student_id', $primero->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertNull(Registration::where('enrollment_id', $enrollment->id)->first());

        $withMatricula = Student::whereHas('enrollments.registration')->count();
        $this->assertGreaterThan(0, $withMatricula);

        // 8) Idempotencia: volver a sembrar no duplica datos
        $nCursos = Course::count();
        $nStudents = Student::count();
        $nAssessments = Assessment::count();
        $nAttendances = Attendance::count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($nCursos, Course::count());
        $this->assertSame($nStudents, Student::count());
        $this->assertSame($nAssessments, Assessment::count());
        $this->assertSame($nAttendances, Attendance::count());
    }
}