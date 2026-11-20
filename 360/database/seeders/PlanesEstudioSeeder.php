<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo oficial de planes de estudio y materias dictadas por paralelo.
 *
 * - Reemplaza por completo el catálogo anterior (subjects) y las asignaciones
 *   (section_subjects). Los subjects se actualizan por (nivel, sigla) para
 *   preservar IDs y no romper horarios/asistencias/diarios existentes.
 * - Los subjects obsoletos sin referencias se eliminan; los referenciados se
 *   conservan y se reportan.
 * - Fuente: planes NIFC, PVCP1, PVCP2-P4, PCPP5, PCPP6, SCPS1-2, SCPS3-5,
 *   SCPS6ABC + tablas de materias dictadas por paralelo.
 */
class PlanesEstudioSeeder extends Seeder
{
    public function run(): void
    {
        $gestion = AcademicYear::where('estado', 'ACTIVA')->first();

        $niveles = [];
        foreach (['Inicial en Familia Comunitaria', 'Primaria Comunitaria Vocacional', 'Secundaria Comunitaria Productiva'] as $nombre) {
            $nivel = Level::where('nombre', $nombre)->first();
            if (! $nivel) {
                $this->command?->error("Nivel inexistente: {$nombre}");

                return;
            }
            $niveles[$nombre] = $nivel;
        }

        $stats = ['subjects_upd' => 0, 'subjects_new' => 0, 'subjects_del' => 0, 'subjects_kept' => [], 'asig' => 0, 'skip_doc' => [], 'skip_sec' => []];

        // 1) Catálogo por (nivel, sigla)
        $vistos = [];
        foreach ($this->planes() as $plan => $datos) {
            $levelId = $niveles[$datos['nivel']]->id;
            foreach ($datos['materias'] as [$codigo, $sigla, $nombre, $area]) {
                $subj = Subject::where('level_id', $levelId)->where('sigla', $sigla)->first();
                if ($subj) {
                    $subj->update(['nombre' => $nombre, 'codigo' => $codigo, 'area' => $area, 'estado' => 'ACTIVO']);
                    $stats['subjects_upd']++;
                } else {
                    $subj = Subject::create([
                        'nombre' => $nombre, 'codigo' => $codigo, 'sigla' => $sigla,
                        'area' => $area, 'level_id' => $levelId, 'estado' => 'ACTIVO',
                    ]);
                    $stats['subjects_new']++;
                }
                $grupo = $subj->grupo ? explode(',', $subj->grupo) : [];
                if (! in_array($plan, $grupo, true)) {
                    $grupo[] = $plan;
                    $subj->update(['grupo' => implode(',', $grupo)]);
                }
                $vistos[$levelId . '|' . $sigla] = true;
            }
        }

        // 1b) Jerarquía padre → hijas (promedios del boletín): el padre
        // promedia las notas de sus hijas y solo él sale en el boletín.
        $jerarquia = [
            'Primaria Comunitaria Vocacional' => ['CL' => ['LELO', 'LE']],
            'Secundaria Comunitaria Productiva' => ['CN' => ['BIO', 'QUIM', 'FIS']],
        ];
        $links = 0;
        foreach ($jerarquia as $nivelNombre => $pares) {
            $nivel = $niveles[$nivelNombre] ?? Level::where('nombre', $nivelNombre)->first();
            if (! $nivel) {
                continue;
            }
            // Aditivo: asegura los links oficiales sin borrar los creados por pantalla.
            foreach ($pares as $padreSigla => $hijasSiglas) {
                $padre = Subject::where('level_id', $nivel->id)->where('sigla', $padreSigla)->first();
                if (! $padre) {
                    $stats['skip_sec'][] = "jerarquía sin padre {$padreSigla} ({$nivelNombre})";
                    continue;
                }
                foreach ($hijasSiglas as $hijaSigla) {
                    $hija = Subject::where('level_id', $nivel->id)->where('sigla', $hijaSigla)->first();
                    if (! $hija) {
                        $stats['skip_sec'][] = "jerarquía sin hija {$hijaSigla} ({$nivelNombre})";
                        continue;
                    }
                    $hija->update(['parent_id' => $padre->id]);
                    $links++;
                }
            }
        }
        $stats['jerarquia'] = $links;

        // 2) Limpieza de asignaciones (cascada limpia planes/criterios/notas)
        SectionSubject::query()->delete();

        // 3) Subjects obsoletos: borrar si nadie los referencia
        foreach (Subject::all() as $subj) {
            if (isset($vistos[$subj->level_id . '|' . $subj->sigla])) {
                continue;
            }
            $refs = DB::table('schedules')->where('subject_id', $subj->id)->count()
                + DB::table('attendances')->where('subject_id', $subj->id)->count()
                + DB::table('diario_aulas')->where('subject_id', $subj->id)->count()
                + DB::table('conducta_reportes')->where('subject_id', $subj->id)->count()
                + DB::table('assessments')->where('subject_id', $subj->id)->count();
            if ($refs === 0) {
                $subj->delete();
                $stats['subjects_del']++;
            } else {
                $stats['subjects_kept'][] = "{$subj->sigla} (nivel {$subj->level_id}, {$refs} refs)";
            }
        }

        // 4) Asignaciones por paralelo (requieren gestión activa)
        if (! $gestion) {
            $this->command?->warn('Sin gestión activa: solo se actualizó el catálogo.');

            return; // catálogo ya quedó aplicado
        }

        $teachers = Teacher::where('estado', 'ACTIVO')->get();
        foreach ($this->dictadas() as $key => $filas) {
            [$gradoNombre, $seccionNombre] = explode('|', $key, 2);
            $grado = Grade::where('nombre', $gradoNombre)->first();
            $section = $grado
                ? Section::where('grade_id', $grado->id)->where('nombre', $seccionNombre)->where('academic_year_id', $gestion->id)->first()
                : null;
            if (! $section) {
                $stats['skip_sec'][] = $key;
                continue;
            }
            foreach ($filas as [$sigla, $docRaw]) {
                if (trim($docRaw) === '' || trim($docRaw) === '-') {
                    continue;
                }
                $subject = Subject::where('level_id', $section->grade->level_id)->where('sigla', $sigla)->first();
                if (! $subject) {
                    $stats['skip_sec'][] = "{$key} sigla {$sigla}";
                    continue;
                }
                $teacher = $this->resolverDocente($docRaw, $teachers);
                if (! $teacher) {
                    $stats['skip_doc'][] = "{$key} {$sigla} <- {$docRaw}";
                    continue;
                }
                SectionSubject::firstOrCreate([
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                ]);
                $stats['asig']++;
            }
        }

        // 5) Completar materia en bloques de horario con (paralelo, docente) único
        $completados = 0;
        foreach (\App\Models\Schedule::whereNotNull('section_id')->whereNotNull('teacher_id')->whereNull('subject_id')->where('estado', 'ACTIVO')->get() as $bloque) {
            $candidatas = SectionSubject::where('section_id', $bloque->section_id)->where('teacher_id', $bloque->teacher_id)->pluck('subject_id')->unique();
            if ($candidatas->count() === 1) {
                $bloque->update(['subject_id' => $candidatas->first()]);
                $completados++;
            }
        }

        $this->command?->info("Planes: {$stats['subjects_upd']} actualizadas, {$stats['subjects_new']} nuevas, {$stats['subjects_del']} obsoletas eliminadas. Jerarquía padre→hija: " . ($stats['jerarquia'] ?? 0) . '.');
        $this->command?->info("Asignaciones creadas: {$stats['asig']}. Bloques de horario completados: {$completados}.");
        foreach (array_unique($stats['subjects_kept']) as $msg) {
            $this->command?->warn("Subject conservado (referenciado): {$msg}");
        }
        foreach (array_unique($stats['skip_sec']) as $msg) {
            $this->command?->warn("Sin paralelo/materia: {$msg}");
        }
        foreach (array_unique($stats['skip_doc']) as $msg) {
            $this->command?->warn("Sin docente: {$msg}");
        }
    }

    private function tokens(string $s): array
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);

        return array_values(array_unique(preg_split('/[^A-Z]+/', $s, -1, PREG_SPLIT_NO_EMPTY)));
    }

    private function planes(): array
    {
        return [
            'NIFC' => ['nivel' => 'Inicial en Familia Comunitaria', 'materias' => [
                ['01', 'DCP', 'Desarrollo del conocimiento y la producción.', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
                ['02', 'CL', 'Comunicación y lenguaje', 'COMUNIDAD Y SOCIEDAD'],
                ['10', 'DBS', 'Desarrollo bio-psico-social', 'VIDA TIERRA TERRITORIO'],
                ['04', 'DSAE', 'Desarrollo sociocultural, afectivo y espiritual', 'COSMOS Y PENSAMIENTO'],
                ['06', 'EF', 'Educación física.', 'DESARROLLO CORPORAL, MOTRIZ Y MUSICAL'],
                ['07', 'MUS', 'Música', 'DESARROLLO CORPORAL, MOTRIZ Y MUSICAL'],
                ['08', 'TEC', 'TECNOLOGÍA', 'EDUCACIÓN TECNOLÓGICA'],
                ['09', 'ING', 'INGLÉS', 'EDUCACIÓN EN IDIOMAS'],
            ]],
            'PVCP1' => ['nivel' => 'Primaria Comunitaria Vocacional', 'materias' => $this->materiasPrimaria()],
            'PVCP2-P4' => ['nivel' => 'Primaria Comunitaria Vocacional', 'materias' => $this->materiasPrimaria()],
            'PCPP5' => ['nivel' => 'Primaria Comunitaria Vocacional', 'materias' => $this->materiasPrimaria()],
            'PCPP6' => ['nivel' => 'Primaria Comunitaria Vocacional', 'materias' => $this->materiasPrimaria()],
            'SCPS1-2' => ['nivel' => 'Secundaria Comunitaria Productiva', 'materias' => [
                ['01', 'LELO', 'LENGUA ESPAÑOLA', 'COMUNIDAD Y SOCIEDAD'],
                ['02', 'LE', 'LENGUA EXTRANJERA', 'COMUNIDAD Y SOCIEDAD'],
                ['03', 'SOC', 'CIENCIAS SOCIALES', 'COMUNIDAD Y SOCIEDAD'],
                ['04', 'EFD', 'EDUCACIÓN FÍSICA Y DEPORTES', 'COMUNIDAD Y SOCIEDAD'],
                ['05', 'EM', 'EDUCACIÓN MUSICAL', 'COMUNIDAD Y SOCIEDAD'],
                ['06', 'APLASVI', 'ARTES PLÁSTICAS Y VISUALES', 'COMUNIDAD Y SOCIEDAD'],
                ['07', 'MAT', 'MATEMÁTICA', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
                ['08', 'TEC', 'TECNOLOGÍA', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
                ['09', 'CN', 'CIENCIAS NATURALES', 'VIDA TIERRA TERRITORIO'],
                ['09.1', 'BIO', 'BIOLOGIA', 'VIDA TIERRA TERRITORIO'],
                ['09.2', 'QUIM', 'QUÍMICA', 'VIDA TIERRA TERRITORIO'],
                ['09.3', 'FIS', 'FÍSICA', 'VIDA TIERRA TERRITORIO'],
                ['10', 'PS', 'PSICOLOGÍA', 'COSMOS Y PENSAMIENTO'],
                ['11', 'VER', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'COSMOS Y PENSAMIENTO'],
            ]],
            'SCPS3-5' => ['nivel' => 'Secundaria Comunitaria Productiva', 'materias' => [
                ['01', 'LELO', 'LENGUA ESPAÑOLA', 'COMUNIDAD Y SOCIEDAD'],
                ['02', 'LE', 'LENGUA EXTRANJERA', 'COMUNIDAD Y SOCIEDAD'],
                ['03', 'SOC', 'CIENCIAS SOCIALES', 'COMUNIDAD Y SOCIEDAD'],
                ['04', 'EFD', 'EDUCACIÓN FÍSICA Y DEPORTES', 'COMUNIDAD Y SOCIEDAD'],
                ['05', 'EM', 'EDUCACIÓN MUSICAL', 'COMUNIDAD Y SOCIEDAD'],
                ['06', 'APLASVI', 'ARTES PLÁSTICAS Y VISUALES', 'COMUNIDAD Y SOCIEDAD'],
                ['07', 'MAT', 'MATEMÁTICA', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
                ['08', 'BIO', 'BIOLOGIA', 'VIDA TIERRA TERRITORIO'],
                ['09', 'FIS', 'FISICA', 'VIDA TIERRA TERRITORIO'],
                ['10', 'QUIM', 'QUIMICA', 'VIDA TIERRA TERRITORIO'],
                ['11', 'CFIL', 'COSMOVISIONES Y FILOSOFÍA', 'COSMOS Y PENSAMIENTO'],
                ['12', 'VER', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'COSMOS Y PENSAMIENTO'],
                ['13', 'TEC', 'TECNOLOGÍA', 'EDUCACIÓN TECNOLÓGICA'],
            ]],
            'SCPS6ABC' => ['nivel' => 'Secundaria Comunitaria Productiva', 'materias' => [
                ['01', 'LELO', 'LENGUA ESPAÑOLA', 'COMUNIDAD Y SOCIEDAD'],
                ['02', 'LE', 'LENGUA EXTRANJERA', 'COMUNIDAD Y SOCIEDAD'],
                ['03', 'SOC', 'CIENCIAS SOCIALES', 'COMUNIDAD Y SOCIEDAD'],
                ['04', 'EFD', 'EDUCACIÓN FÍSICA Y DEPORTES', 'COMUNIDAD Y SOCIEDAD'],
                ['05', 'EM', 'EDUCACIÓN MUSICAL', 'COMUNIDAD Y SOCIEDAD'],
                ['06', 'APLASVI', 'ARTES PLÁSTICAS Y VISUALES', 'COMUNIDAD Y SOCIEDAD'],
                ['07', 'MAT', 'MATEMÁTICA', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
                ['08', 'BIO', 'BIOLOGÍA', 'VIDA TIERRA TERRITORIO'],
                ['09', 'FIS', 'FISICA', 'VIDA TIERRA TERRITORIO'],
                ['10', 'QUIM', 'QUÍMICA', 'VIDA TIERRA TERRITORIO'],
                ['11', 'CFIL', 'COSMOVISIONES Y FILOSOFÍA', 'COSMOS Y PENSAMIENTO'],
                ['12', 'VER', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'COSMOS Y PENSAMIENTO'],
                ['13', 'TEC', 'TECNOLOGÍA', 'EDUCACIÓN TECNOLÓGICA'],
                ['14', 'INV', 'INVESTIGACIÓN CIENTÍFICA', 'COMPLEMENTARIAS'],
                ['15', 'EMP', 'EMPRENDIMIENTO', 'COMPLEMENTARIAS'],
            ]],
        ];
    }

    private function materiasPrimaria(): array
    {
        return [
            ['01', 'CL', 'COMUNICACIÓN Y LENGUAJES', 'COMUNIDAD Y SOCIEDAD'],
            ['01.01', 'LELO', 'Lengua española', 'COMUNIDAD Y SOCIEDAD'],
            ['01.02', 'LE', 'Lengua extranjera', 'COMUNIDAD Y SOCIEDAD'],
            ['02', 'SOC', 'CIENCIAS SOCIALES', 'COMUNIDAD Y SOCIEDAD'],
            ['03', 'EFD', 'EDUCACIÓN FÍSICA Y DEPORTES', 'COMUNIDAD Y SOCIEDAD'],
            ['04', 'EM', 'EDUCACIÓN MUSICAL', 'COMUNIDAD Y SOCIEDAD'],
            ['05', 'APLASVI', 'ARTES PLÁSTICAS Y VISUALES', 'COMUNIDAD Y SOCIEDAD'],
            ['06', 'MAT', 'MATEMÁTICA', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
            ['07', 'TEC', 'TECNOLOGÍA', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN'],
            ['08', 'CN', 'CIENCIAS NATURALES', 'VIDA TIERRA TERRITORIO'],
            ['09', 'VER', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'COSMOS Y PENSAMIENTO'],
        ];
    }

    private function dictadas(): array
    {
        $ini = [
            '1ro Inicial|A' => [
                ['DCP', 'DORADO MEDRANO CLAUDIA NOELIA'], ['CL', 'DORADO MEDRANO CLAUDIA NOELIA'],
                ['DBS', 'DORADO MEDRANO CLAUDIA NOELIA'], ['DSAE', '-'],
                ['EF', 'CHACON MARTINEZ MARCIA JOHANNE'], ['MUS', 'GUERRERO MONZON VALERIA INES'],
                ['TEC', 'MENDOZA ACOSTA ERLAN GUALBERTO'], ['ING', 'ARIAS LUPA JANETH MARIA'],
            ],
            '1ro Inicial|B' => [
                ['DCP', 'REYES CORTEZ CLAUDIA'], ['CL', 'REYES CORTEZ CLAUDIA'],
                ['DBS', 'REYES CORTEZ CLAUDIA'], ['DSAE', '-'],
                ['EF', 'CHACON MARTINEZ MARCIA JOHANNE'], ['MUS', 'GUERRERO MONZON VALERIA INES'],
                ['TEC', 'MENDOZA ACOSTA ERLAN GUALBERTO'], ['ING', 'ARIAS LUPA JANETH MARIA'],
            ],
            // Bloque sin título en la fuente: corresponde a Segundo A (único Inicial sin tabla propia)
            '2do Inicial|A' => [
                ['DCP', 'MARTINEZ ORTEGA ARACELY CAROL'], ['CL', 'MARTINEZ ORTEGA ARACELY CAROL'],
                ['DBS', 'MARTINEZ ORTEGA ARACELY CAROL'], ['DSAE', '-'],
                ['EF', 'CHACON MARTINEZ MARCIA JOHANNE'], ['MUS', 'GUERRERO MONZON VALERIA INES'],
                ['TEC', 'MENDOZA ACOSTA ERLAN GUALBERTO'], ['ING', 'TAPIA PEREZ VERONICA'],
            ],
            '2do Inicial|B' => [
                ['DCP', 'TOLAY SOLANO CLAUDIA'], ['CL', 'TOLAY SOLANO CLAUDIA'],
                ['DBS', 'TOLAY SOLANO CLAUDIA'], ['DSAE', '-'],
                ['EF', 'CHACON MARTINEZ MARCIA JOHANNE'], ['MUS', 'GUERRERO MONZON VALERIA INES'],
                ['TEC', 'MENDOZA ACOSTA ERLAN GUALBERTO'], ['ING', 'TAPIA PEREZ VERONICA'],
            ],
        ];

        $d = [];

        // Primaria 1ro-3ro: tutora dicta CL/LELO/SOC/MAT/CN/VER; LE siempre Tapia Verónica;
        // EFD Cruz Borges; EM Guerrero; APLASVI Rueda; TEC Aguilar (desde 2do).
        $primariaBaja = [
            '1ro Primaria|A' => ['TAPIA JURADO VIVIANA LIZ', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'MENDOZA ACOSTA ERLAN GUALBERTO'],
            '1ro Primaria|B' => ['MICHAGA SANCHEZ MARY ISABEL', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'MENDOZA ACOSTA ERLAN GUALBERTO'],
            '1ro Primaria|C' => ['Valdez Ayarde Liliana Hilda', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'MENDOZA ACOSTA ERLAN GUALBERTO'],
            '2do Primaria|A' => ['ALFARO ARANDA ELVY YOVANA', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'AGUILAR URBANO FRANZ DANIEL'],
            '2do Primaria|B' => ['SORUCO ROSSEL MARIA LUISA', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'AGUILAR URBANO FRANZ DANIEL'],
            '2do Primaria|C' => ['CUEVAS TORREZ GIOVANNA', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'AGUILAR URBANO FRANZ DANIEL'],
            '3ro Primaria|A' => ['JURADO FERNANDEZ LILIANA', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'AGUILAR URBANO FRANZ DANIEL'],
            '3ro Primaria|B' => ['ORTEGA FARFAN EVA FANI', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'GUERRERO MONZON VALERIA INES', 'AGUILAR URBANO FRANZ DANIEL'],
            '3ro Primaria|C' => ['ALVAREZ COLQUE GABY', 'TAPIA PEREZ VERONICA', 'CRUZ BORGES EMILIO', 'ALDANA ROJAS YOLANDA', 'AGUILAR URBANO FRANZ DANIEL'],
        ];
        // [tutor(CL/LELO/SOC/MAT/CN/VER), LE, EFD, EM/APLASVI-varía, TEC]
        foreach ($primariaBaja as $key => [$tutor, $le, $efd, $em, $tec]) {
            $emApl = $key === '3ro Primaria|C' ? 'ALDANA ROJAS YOLANDA' : $em;
            $d[$key] = [
                ['CL', $tutor], ['LELO', $tutor], ['LE', $le], ['SOC', $tutor],
                ['EFD', $efd], ['EM', $emApl], ['APLASVI', 'RUEDA LEIGUE MARLY'],
                ['MAT', $tutor], ['TEC', $tec], ['CN', $tutor], ['VER', $tutor],
            ];
        }

        // Primaria 4to-6to: EFD Cruz; APLASVI Romero; TEC Aguilar; LE Rivera (4to-6to)
        $primariaAlta = [
            // [tutor(CL/LELO/SOC/MAT/CN/VER), EM, MAT-extra?, CN-extra?]
            '4to Primaria|A' => ['VELASQUEZ NAVARRO GABRIELA', 'ALDANA ROJAS YOLANDA', null, null],
            '4to Primaria|B' => ['SANCHEZ COPA VICTOR HUGO', 'ALDANA ROJAS YOLANDA', null, null],
            '4to Primaria|C' => ['YAÑEZ QUIROGA SHIRLEY JANETH', 'ALDANA ROJAS YOLANDA', null, null],
            '5to Primaria|A' => ['CAYO QUISPE MARISOL ANTONIA', 'ALDANA ROJAS YOLANDA', 'LOPEZ VILCA ILSEN', 'VELASQUEZ VEGA ARMANDO EMILIO'],
            '5to Primaria|B' => ['CAYO QUISPE MARISOL ANTONIA', 'ALDANA ROJAS YOLANDA', 'LOPEZ VILCA ILSEN', 'VELASQUEZ VEGA ARMANDO EMILIO'],
            '5to Primaria|C' => ['CAYO QUISPE MARISOL ANTONIA', 'ALDANA ROJAS YOLANDA', 'LOPEZ VILCA ILSEN', 'VELASQUEZ VEGA ARMANDO EMILIO'],
            '6to Primaria|A' => ['HOYOS RODRIGUEZ ROSA ELENA', 'ALDANA ROJAS YOLANDA', 'VELASQUEZ VEGA ARMANDO EMILIO', 'CAYO QUISPE MARISOL ANTONIA'],
            '6to Primaria|B' => ['HOYOS RODRIGUEZ ROSA ELENA', 'ALDANA ROJAS YOLANDA', 'VELASQUEZ VEGA ARMANDO EMILIO', 'LOPEZ VILCA ILSEN'],
            '6to Primaria|C' => ['HOYOS RODRIGUEZ ROSA ELENA', 'ALDANA ROJAS YOLANDA', 'VELASQUEZ VEGA ARMANDO EMILIO', 'LOPEZ VILCA ILSEN'],
        ];
        foreach ($primariaAlta as $key => [$tutor, $em, $mat, $cn]) {
            $grado = explode('|', $key)[0];
            $esQuintoOSexto = str_starts_with($grado, '5to') || str_starts_with($grado, '6to');
            $socVer = $esQuintoOSexto ? 'BALDIVIEZO PONCE PAULO CESAR' : $tutor;
            // LE: en 4to la dicta el tutor; en 5to-6to Rivera
            $le = str_starts_with($grado, '4to') ? $tutor : 'RIVERA MELENDRES ZAIDA VICTORIA';
            $d[$key] = [
                ['CL', $tutor], ['LELO', $tutor], ['LE', $le], ['SOC', $socVer],
                ['EFD', 'CRUZ BORGES EMILIO'], ['EM', $em], ['APLASVI', 'ROMERO JARAMILLO JUAN CARLOS'],
                ['MAT', $mat ?? $tutor], ['TEC', 'AGUILAR URBANO FRANZ DANIEL'], ['CN', $cn ?? $tutor], ['VER', $socVer],
            ];
        }
        // Sexto B: Lengua extranjera con dos docentes
        $d['6to Primaria|B'][] = ['LE', 'ARIAS LUPA JANETH MARIA'];

        return array_merge($ini, $d, $this->dictadasSecundaria());
    }

    private function dictadasSecundaria(): array
    {
        // [LELO, LE, SOC, EFD, EM, APLASVI, MAT, CN/BIO/FIS/QUIM..., VER, TEC, ...]
        $base12 = [
            '1ro Secundaria|A' => ['SORUCO GUERRERO FATIMA DOLORES', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', 'BURGOS DAVILA GREGORIO JAVIER', 'LIMA CORTEZ DAVID ANTONIO', 'FLORES MAMANI GLADYS BENERANDA', 'LOPEZ LUCAS MARCELA HERMI', 'MENDOZA ACOSTA ERLAN GUALBERTO', 'HERRERA - RICARDO RAMON'],
            '1ro Secundaria|B' => ['SORUCO GUERRERO FATIMA DOLORES', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', 'BURGOS DAVILA GREGORIO JAVIER', 'LIMA CORTEZ DAVID ANTONIO', 'FLORES MAMANI GLADYS BENERANDA', 'LOPEZ LUCAS MARCELA HERMI', 'MENDOZA ACOSTA ERLAN GUALBERTO', 'HERRERA - RICARDO RAMON'],
            '1ro Secundaria|C' => ['SORUCO GUERRERO FATIMA DOLORES', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', 'BURGOS DAVILA GREGORIO JAVIER', 'LIMA CORTEZ DAVID ANTONIO', 'FLORES MAMANI GLADYS BENERANDA', 'LOPEZ LUCAS MARCELA HERMI', 'MENDOZA ACOSTA ERLAN GUALBERTO', 'HERRERA - RICARDO RAMON'],
            '2do Secundaria|A' => ['SORUCO GUERRERO FATIMA DOLORES', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', 'BURGOS DAVILA GREGORIO JAVIER', 'LIMA CORTEZ DAVID ANTONIO', 'FLORES MAMANI GLADYS BENERANDA', 'DURAN HUARACHI OSMAR SAUL', 'MENDOZA ACOSTA ERLAN GUALBERTO', 'HERRERA - RICARDO RAMON'],
            '2do Secundaria|B' => ['SORUCO GUERRERO FATIMA DOLORES', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', 'BURGOS DAVILA GREGORIO JAVIER', 'LIMA CORTEZ DAVID ANTONIO', 'FLORES MAMANI GLADYS BENERANDA', 'LOPEZ LUCAS MARCELA HERMI', 'MENDOZA ACOSTA ERLAN GUALBERTO', 'HERRERA - RICARDO RAMON'],
            '2do Secundaria|C' => ['SORUCO GUERRERO FATIMA DOLORES', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', 'BURGOS DAVILA GREGORIO JAVIER', 'LIMA CORTEZ DAVID ANTONIO', 'FLORES MAMANI GLADYS BENERANDA', 'DURAN HUARACHI OSMAR SAUL', 'MENDOZA ACOSTA ERLAN GUALBERTO', 'HERRERA - RICARDO RAMON'],
        ];
        // [lelo, le, soc, efd, em, aplasvi, mat, bio/fis/quim..., cfil/tec...]
        $d = [];
        foreach ($base12 as $key => [$lelo, $le, $soc, $efd, $em, $aplasvi, $mat, $tec, $ver]) {
            $esPrimero = str_starts_with(explode('|', $key)[0], '1ro');
            $d[$key] = [
                ['LELO', $lelo], ['LE', $le], ['SOC', $soc], ['EFD', $efd], ['EM', $em], ['APLASVI', $aplasvi],
                ['MAT', $mat], ['CN', 'PELAEZ VEGA IRMA JOSEFINA'],
                ['BIO', 'PELAEZ VEGA IRMA JOSEFINA'], ['QUIM', 'PACARA CRUZ MERY LAURA'], ['FIS', 'DORIA MEDINA CORTEZ MIGUEL ANGEL'],
                ['PS', 'BALDIVIEZO PONCE PAULO CESAR'], ['VER', $ver], ['TEC', $tec],
            ];
        }

        // S3: MAT/BIO/FIS propios; resto común (LELO Eva, LE Rivera, SOC Aparicio, TEC Erlan...)
        $s3 = [
            '3ro Secundaria|A' => ['SARAVIA GARZON ERICK EDUARDO', 'PELAEZ VEGA IRMA JOSEFINA', 'DORIA MEDINA CORTEZ MIGUEL ANGEL'],
            '3ro Secundaria|B' => ['SARAVIA GARZON ERICK EDUARDO', 'PELAEZ VEGA IRMA JOSEFINA', 'DORIA MEDINA CORTEZ MIGUEL ANGEL'],
            '3ro Secundaria|C' => ['SARAVIA GARZON ERICK EDUARDO', 'MERCADO AMPUERO HENRY', 'ORTEGA ZEBALLOS LUIS AUGUSTO'],
        ];
        foreach ($s3 as $key => [$mat, $bio, $fis]) {
            $d[$key] = $this->filaS3('MENDOZA COLQUE EVA MARIELA', 'RIVERA MELENDRES ZAIDA VICTORIA', 'APARICIO VERDUN SERGIO', $mat, $bio, $fis);
        }
        // S4-S5 explícitos: [lelo, le, soc, aplasvi, mat, bio, fis, quim]
        $s45 = [
            '4to Secundaria|A' => ['MENDOZA COLQUE EVA MARIELA', 'ARIAS LUPA JANETH MARIA', 'TAMARES SANABRIA JUAN CARLOS', 'VIDEZ SANCHEZ JONATHANREYNALDO', 'DURAN HUARACHI OSMAR SAUL', 'PELAEZ VEGA IRMA JOSEFINA', 'DORIA MEDINA CORTEZ MIGUEL ANGEL', 'VIDAURRE PEREZ CINTHYA NOEMI'],
            '4to Secundaria|B' => ['MENDOZA COLQUE EVA MARIELA', 'ARIAS LUPA JANETH MARIA', 'TAMARES SANABRIA JUAN CARLOS', 'VIDEZ SANCHEZ JONATHANREYNALDO', 'DURAN HUARACHI OSMAR SAUL', 'PELAEZ VEGA IRMA JOSEFINA', 'DORIA MEDINA CORTEZ MIGUEL ANGEL', 'VIDAURRE PEREZ CINTHYA NOEMI'],
            '4to Secundaria|C' => ['MENDOZA COLQUE EVA MARIELA', 'ARIAS LUPA JANETH MARIA', 'TAMARES SANABRIA JUAN CARLOS', 'VIDEZ SANCHEZ JONATHANREYNALDO', 'MERCADO AMPUERO HENRY', 'MERCADO AMPUERO HENRY', 'ORTEGA ZEBALLOS LUIS AUGUSTO', 'VIDAURRE PEREZ CINTHYA NOEMI'],
            '5to Secundaria|A - BQS' => ['MENDOZA COLQUE EVA MARIELA', 'ARIAS LUPA JANETH MARIA', 'TAMARES SANABRIA JUAN CARLOS', 'VIDEZ SANCHEZ JONATHANREYNALDO', 'SARAVIA GARZON ERICK EDUARDO', 'MERCADO AMPUERO HENRY', 'CALLAPA MEDRANO ALVARO BERNARDO', 'VACAFLOR JIJENA CLAUDIA EVELIN'],
            '5to Secundaria|B - ASP' => ['URZAGASTE ALFARO MARINA', 'ARIAS LUPA JANETH MARIA', 'TAMARES SANABRIA JUAN CARLOS', 'VIDEZ SANCHEZ JONATHANREYNALDO', 'SARAVIA GARZON ERICK EDUARDO', 'MERCADO AMPUERO HENRY', 'ORTEGA ZEBALLOS LUIS AUGUSTO', 'VACAFLOR JIJENA CLAUDIA EVELIN'],
            '5to Secundaria|C - CE' => ['URZAGASTE ALFARO MARINA', 'ARIAS LUPA JANETH MARIA', 'TAMARES SANABRIA JUAN CARLOS', 'VIDEZ SANCHEZ JONATHANREYNALDO', 'VACA GOMEZ BERTHY', 'MERCADO AMPUERO HENRY', 'CALLAPA MEDRANO ALVARO BERNARDO', 'VACAFLOR JIJENA CLAUDIA EVELIN'],
        ];
        foreach ($s45 as $key => [$lelo, $le, $soc, $aplasvi, $mat, $bio, $fis, $quim]) {
            $d[$key] = $this->filaS45($lelo, $le, $soc, $aplasvi, $mat, $bio, $fis, $quim);
        }

        // S6: MAT Vaca; BIO Mercado; FIS Callapa; QUIM Vacaflor; CFIL Peralta (A/B) o - (A/C); VER Herrera; TEC Ruiz; INV Aparicio; EMP -
        foreach (['6to Secundaria|A - BQS', '6to Secundaria|B - ASP', '6to Secundaria|C - CE'] as $key) {
            $cfil = $key === '6to Secundaria|B - ASP' ? 'PERALTA LOPEZ LUCAS MILTON' : '-';
            $d[$key] = [
                ['LELO', 'URZAGASTE ALFARO MARINA'], ['LE', 'ARIAS LUPA JANETH MARIA'],
                ['SOC', 'TAMARES SANABRIA JUAN CARLOS'], ['EFD', 'BURGOS DAVILA GREGORIO JAVIER'],
                ['EM', 'LIMA CORTEZ DAVID ANTONIO'], ['APLASVI', 'VIDEZ SANCHEZ JONATHANREYNALDO'],
                ['MAT', 'VACA GOMEZ BERTHY'], ['BIO', 'MERCADO AMPUERO HENRY'],
                ['FIS', 'CALLAPA MEDRANO ALVARO BERNARDO'], ['QUIM', 'VACAFLOR JIJENA CLAUDIA EVELIN'],
                ['CFIL', $cfil], ['VER', 'HERRERA - RICARDO RAMON'], ['TEC', 'RUIZ GAITE ROBERTO CARLOS'],
                ['INV', 'APARICIO VERDUN SERGIO'], ['EMP', '-'],
            ];
        }
        // Sexto B: Lengua extranjera con dos docentes
        $d['6to Secundaria|B - ASP'][] = ['LE', 'RIVERA MELENDRES ZAIDA VICTORIA'];

        return $d;
    }

    private function filaS3(string $lelo, string $le, string $soc, string $mat, string $bio, string $fis): array
    {
        return [
            ['LELO', $lelo], ['LE', $le],
            ['SOC', $soc], ['EFD', 'BURGOS DAVILA GREGORIO JAVIER'],
            ['EM', 'LIMA CORTEZ DAVID ANTONIO'], ['APLASVI', 'FLORES MAMANI GLADYS BENERANDA'],
            ['MAT', $mat], ['BIO', $bio], ['FIS', $fis], ['QUIM', 'VIDAURRE PEREZ CINTHYA NOEMI'],
            ['CFIL', 'PERALTA LOPEZ LUCAS MILTON'], ['VER', 'HERRERA - RICARDO RAMON'],
            ['TEC', 'MENDOZA ACOSTA ERLAN GUALBERTO'],
        ];
    }

    private function filaS45(string $lelo, string $le, string $soc, string $aplasvi, string $mat, string $bio, string $fis, string $quim): array
    {
        return [
            ['LELO', $lelo], ['LE', $le],
            ['SOC', $soc], ['EFD', 'BURGOS DAVILA GREGORIO JAVIER'],
            ['EM', 'LIMA CORTEZ DAVID ANTONIO'], ['APLASVI', $aplasvi],
            ['MAT', $mat], ['BIO', $bio], ['FIS', $fis], ['QUIM', $quim],
            ['CFIL', 'PERALTA LOPEZ LUCAS MILTON'], ['VER', 'HERRERA - RICARDO RAMON'],
            ['TEC', 'MENDOZA ACOSTA ERLAN GUALBERTO'],
        ];
    }

    private function resolverDocente(string $raw, $teachers): ?Teacher
    {
        $limpio = trim(str_replace(['-', '  '], [' ', ' '], $raw));
        // En BD figura como JONATHANREYNALDO (pegado): match por apellido VIDEZ
        if (str_contains(mb_strtoupper($limpio), 'VIDEZ')) {
            $porApellido = $teachers->filter(fn ($t) => str_contains(mb_strtoupper($t->apellidos), 'VIDEZ'));

            return $porApellido->count() === 1 ? $porApellido->first() : null;
        }
        $toks = $this->tokens($limpio);
        $cands = $teachers->filter(fn ($t) => empty(array_diff($toks, $this->tokens($t->nombres . ' ' . $t->apellidos))));

        return $cands->count() === 1 ? $cands->first() : null;
    }
}
