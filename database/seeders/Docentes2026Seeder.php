<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Docentes2026Seeder extends Seeder
{
    public function run(): void
    {
        // Docentes oficiales 2026 (importados de docentes_20261.xlsx)
        $docentes = [
    0 => [
    'codigo' => 'DOC-0001',
    'nombres' => 'JANETH MARIA',
    'apellidos' => 'ARIAS LUPA',
    'especialidad' => 'INGLÉS, Lengua extranjera, LENGUA EXTRANJERA',
    'numero_documento' => '90000001',
  ],
    1 => [
    'codigo' => 'DOC-0002',
    'nombres' => 'MARCIA JOHANNE',
    'apellidos' => 'CHACON MARTINEZ',
    'especialidad' => 'Educación física., EDUCACIÓN FÍSICA Y DEPORTES',
    'numero_documento' => '90000002',
  ],
    2 => [
    'codigo' => 'DOC-0003',
    'nombres' => 'CLAUDIA NOELIA',
    'apellidos' => 'DORADO MEDRANO',
    'especialidad' => 'Comunicación y lenguaje, Desarrollo bio-psico-social, Desarrollo del conocimiento y la producción.',
    'numero_documento' => '90000003',
  ],
    3 => [
    'codigo' => 'DOC-0004',
    'nombres' => 'VALERIA INES',
    'apellidos' => 'GUERRERO MONZON',
    'especialidad' => 'Música, EDUCACIÓN MUSICAL',
    'numero_documento' => '90000004',
  ],
    4 => [
    'codigo' => 'DOC-0005',
    'nombres' => 'ERLAN GUALBERTO',
    'apellidos' => 'MENDOZA ACOSTA',
    'especialidad' => 'TECNOLOGÍA',
    'numero_documento' => '90000005',
  ],
    5 => [
    'codigo' => 'DOC-0006',
    'nombres' => 'CLAUDIA',
    'apellidos' => 'REYES CORTEZ',
    'especialidad' => 'Comunicación y lenguaje, Desarrollo bio-psico-social, Desarrollo del conocimiento y la producción.',
    'numero_documento' => '90000006',
  ],
    6 => [
    'codigo' => 'DOC-0007',
    'nombres' => 'ARACELY CAROL',
    'apellidos' => 'MARTINEZ ORTEGA',
    'especialidad' => 'Comunicación y lenguaje, Desarrollo bio-psico-social, Desarrollo del conocimiento y la producción.',
    'numero_documento' => '90000007',
  ],
    7 => [
    'codigo' => 'DOC-0008',
    'nombres' => 'VERONICA',
    'apellidos' => 'TAPIA PEREZ',
    'especialidad' => 'INGLÉS, Lengua extranjera',
    'numero_documento' => '90000008',
  ],
    8 => [
    'codigo' => 'DOC-0009',
    'nombres' => 'CLAUDIA',
    'apellidos' => 'TOLAY SOLANO',
    'especialidad' => 'Comunicación y lenguaje, Desarrollo bio-psico-social, Desarrollo del conocimiento y la producción.',
    'numero_documento' => '90000009',
  ],
    9 => [
    'codigo' => 'DOC-0010',
    'nombres' => 'MARLY',
    'apellidos' => 'RUEDA LEIGUE',
    'especialidad' => 'ARTES PLÁSTICAS Y VISUALES',
    'numero_documento' => '90000010',
  ],
    10 => [
    'codigo' => 'DOC-0011',
    'nombres' => 'VIVIANA LIZ',
    'apellidos' => 'TAPIA JURADO',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000011',
  ],
    11 => [
    'codigo' => 'DOC-0012',
    'nombres' => 'MARY ISABEL',
    'apellidos' => 'MICHAGA SANCHEZ',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000012',
  ],
    12 => [
    'codigo' => 'DOC-0013',
    'nombres' => 'LILIANA HILDA',
    'apellidos' => 'VALDEZ AYARDE',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000013',
  ],
    13 => [
    'codigo' => 'DOC-0014',
    'nombres' => 'FRANZ DANIEL',
    'apellidos' => 'AGUILAR URBANO',
    'especialidad' => 'TECNOLOGÍA',
    'numero_documento' => '90000014',
  ],
    14 => [
    'codigo' => 'DOC-0015',
    'nombres' => 'ELVY YOVANA',
    'apellidos' => 'ALFARO ARANDA',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000015',
  ],
    15 => [
    'codigo' => 'DOC-0016',
    'nombres' => 'EMILIO',
    'apellidos' => 'CRUZ BORGES',
    'especialidad' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'numero_documento' => '90000016',
  ],
    16 => [
    'codigo' => 'DOC-0017',
    'nombres' => 'MARIA LUISA',
    'apellidos' => 'SORUCO ROSSEL',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000017',
  ],
    17 => [
    'codigo' => 'DOC-0018',
    'nombres' => 'GIOVANNA',
    'apellidos' => 'CUEVAS TORREZ',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000018',
  ],
    18 => [
    'codigo' => 'DOC-0019',
    'nombres' => 'LILIANA',
    'apellidos' => 'JURADO FERNANDEZ',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000019',
  ],
    19 => [
    'codigo' => 'DOC-0020',
    'nombres' => 'EVA FANI',
    'apellidos' => 'ORTEGA FARFAN',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000020',
  ],
    20 => [
    'codigo' => 'DOC-0021',
    'nombres' => 'YOLANDA',
    'apellidos' => 'ALDANA ROJAS',
    'especialidad' => 'EDUCACIÓN MUSICAL',
    'numero_documento' => '90000021',
  ],
    21 => [
    'codigo' => 'DOC-0022',
    'nombres' => 'GABY',
    'apellidos' => 'ALVAREZ COLQUE',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000022',
  ],
    22 => [
    'codigo' => 'DOC-0023',
    'nombres' => 'ZAIDA VICTORIA',
    'apellidos' => 'RIVERA MELENDRES',
    'especialidad' => 'Lengua extranjera, LENGUA EXTRANJERA',
    'numero_documento' => '90000023',
  ],
    23 => [
    'codigo' => 'DOC-0024',
    'nombres' => 'JUAN CARLOS',
    'apellidos' => 'ROMERO JARAMILLO',
    'especialidad' => 'ARTES PLÁSTICAS Y VISUALES',
    'numero_documento' => '90000024',
  ],
    24 => [
    'codigo' => 'DOC-0025',
    'nombres' => 'GABRIELA',
    'apellidos' => 'VELASQUEZ NAVARRO',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000025',
  ],
    25 => [
    'codigo' => 'DOC-0026',
    'nombres' => 'VICTOR HUGO',
    'apellidos' => 'SANCHEZ COPA',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000026',
  ],
    26 => [
    'codigo' => 'DOC-0027',
    'nombres' => 'SHIRLEY JANETH',
    'apellidos' => 'YAÑEZ QUIROGA',
    'especialidad' => 'CIENCIAS NATURALES, CIENCIAS SOCIALES, COMUNICACIÓN Y LENGUAJES, Lengua española, MATEMÁTICA, VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000027',
  ],
    27 => [
    'codigo' => 'DOC-0028',
    'nombres' => 'PAULO CESAR',
    'apellidos' => 'BALDIVIEZO PONCE',
    'especialidad' => 'CIENCIAS SOCIALES, VALORES, ESPIRITUALIDADES Y RELIGIONES, PSICOLOGÍA',
    'numero_documento' => '90000028',
  ],
    28 => [
    'codigo' => 'DOC-0029',
    'nombres' => 'MARISOL ANTONIA',
    'apellidos' => 'CAYO QUISPE',
    'especialidad' => 'COMUNICACIÓN Y LENGUAJES, Lengua española, CIENCIAS NATURALES',
    'numero_documento' => '90000029',
  ],
    29 => [
    'codigo' => 'DOC-0030',
    'nombres' => 'ILSEN',
    'apellidos' => 'LOPEZ VILCA',
    'especialidad' => 'MATEMÁTICA, CIENCIAS NATURALES',
    'numero_documento' => '90000030',
  ],
    30 => [
    'codigo' => 'DOC-0031',
    'nombres' => 'ARMANDO EMILIO',
    'apellidos' => 'VELASQUEZ VEGA',
    'especialidad' => 'CIENCIAS NATURALES, MATEMÁTICA',
    'numero_documento' => '90000031',
  ],
    31 => [
    'codigo' => 'DOC-0032',
    'nombres' => 'ROSA ELENA',
    'apellidos' => 'HOYOS RODRIGUEZ',
    'especialidad' => 'COMUNICACIÓN Y LENGUAJES, Lengua española',
    'numero_documento' => '90000032',
  ],
    32 => [
    'codigo' => 'DOC-0033',
    'nombres' => 'SERGIO',
    'apellidos' => 'APARICIO VERDUN',
    'especialidad' => 'CIENCIAS SOCIALES, INVESTIGACIÓN CIENTÍFICA',
    'numero_documento' => '90000033',
  ],
    33 => [
    'codigo' => 'DOC-0034',
    'nombres' => 'GREGORIO JAVIER',
    'apellidos' => 'BURGOS DAVILA',
    'especialidad' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'numero_documento' => '90000034',
  ],
    34 => [
    'codigo' => 'DOC-0035',
    'nombres' => 'MIGUEL ANGEL',
    'apellidos' => 'DORIA MEDINA CORTEZ',
    'especialidad' => 'FÍSICA, FISICA',
    'numero_documento' => '90000035',
  ],
    35 => [
    'codigo' => 'DOC-0036',
    'nombres' => 'GLADYS BENERANDA',
    'apellidos' => 'FLORES MAMANI',
    'especialidad' => 'ARTES PLÁSTICAS Y VISUALES',
    'numero_documento' => '90000036',
  ],
    36 => [
    'codigo' => 'DOC-0037',
    'nombres' => 'RICARDO RAMON',
    'apellidos' => 'HERRERA',
    'especialidad' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'numero_documento' => '90000037',
  ],
    37 => [
    'codigo' => 'DOC-0038',
    'nombres' => 'DAVID ANTONIO',
    'apellidos' => 'LIMA CORTEZ',
    'especialidad' => 'EDUCACIÓN MUSICAL',
    'numero_documento' => '90000038',
  ],
    38 => [
    'codigo' => 'DOC-0039',
    'nombres' => 'MARCELA HERMI',
    'apellidos' => 'LOPEZ LUCAS',
    'especialidad' => 'MATEMÁTICA',
    'numero_documento' => '90000039',
  ],
    39 => [
    'codigo' => 'DOC-0040',
    'nombres' => 'MERY LAURA',
    'apellidos' => 'PACARA CRUZ',
    'especialidad' => 'QUÍMICA',
    'numero_documento' => '90000040',
  ],
    40 => [
    'codigo' => 'DOC-0041',
    'nombres' => 'IRMA JOSEFINA',
    'apellidos' => 'PELAEZ VEGA',
    'especialidad' => 'BIOLOGIA, CIENCIAS NATURALES',
    'numero_documento' => '90000041',
  ],
    41 => [
    'codigo' => 'DOC-0042',
    'nombres' => 'FATIMA DOLORES',
    'apellidos' => 'SORUCO GUERRERO',
    'especialidad' => 'LENGUA ESPAÑOLA',
    'numero_documento' => '90000042',
  ],
    42 => [
    'codigo' => 'DOC-0043',
    'nombres' => 'OSMAR SAUL',
    'apellidos' => 'DURAN HUARACHI',
    'especialidad' => 'MATEMÁTICA',
    'numero_documento' => '90000043',
  ],
    43 => [
    'codigo' => 'DOC-0044',
    'nombres' => 'LUCAS MILTON',
    'apellidos' => 'PERALTA LOPEZ',
    'especialidad' => 'COSMOVISIONES Y FILOSOFÍA',
    'numero_documento' => '90000044',
  ],
    44 => [
    'codigo' => 'DOC-0045',
    'nombres' => 'MARTHA INES',
    'apellidos' => 'SANCHEZ IRIARTE',
    'especialidad' => 'LENGUA ESPAÑOLA',
    'numero_documento' => '90000045',
  ],
    45 => [
    'codigo' => 'DOC-0046',
    'nombres' => 'ERICK EDUARDO',
    'apellidos' => 'SARAVIA GARZON',
    'especialidad' => 'MATEMÁTICA',
    'numero_documento' => '90000046',
  ],
    46 => [
    'codigo' => 'DOC-0047',
    'nombres' => 'CINTHYA NOEMI',
    'apellidos' => 'VIDAURRE PEREZ',
    'especialidad' => 'QUIMICA',
    'numero_documento' => '90000047',
  ],
    47 => [
    'codigo' => 'DOC-0048',
    'nombres' => 'HENRY',
    'apellidos' => 'MERCADO AMPUERO',
    'especialidad' => 'BIOLOGIA, BIOLOGÍA',
    'numero_documento' => '90000048',
  ],
    48 => [
    'codigo' => 'DOC-0049',
    'nombres' => 'LUIS AUGUSTO',
    'apellidos' => 'ORTEGA ZEBALLOS',
    'especialidad' => 'FISICA',
    'numero_documento' => '90000049',
  ],
    49 => [
    'codigo' => 'DOC-0050',
    'nombres' => 'JUAN CARLOS',
    'apellidos' => 'TAMARES SANABRIA',
    'especialidad' => 'CIENCIAS SOCIALES',
    'numero_documento' => '90000050',
  ],
    50 => [
    'codigo' => 'DOC-0051',
    'nombres' => 'JONATHANREYNALDO',
    'apellidos' => 'VIDEZ SANCHEZ',
    'especialidad' => 'ARTES PLÁSTICAS Y VISUALES',
    'numero_documento' => '90000051',
  ],
    51 => [
    'codigo' => 'DOC-0052',
    'nombres' => 'ALVARO BERNARDO',
    'apellidos' => 'CALLAPA MEDRANO',
    'especialidad' => 'FISICA',
    'numero_documento' => '90000052',
  ],
    52 => [
    'codigo' => 'DOC-0053',
    'nombres' => 'CLAUDIA EVELIN',
    'apellidos' => 'VACAFLOR JIJENA',
    'especialidad' => 'QUIMICA, QUÍMICA',
    'numero_documento' => '90000053',
  ],
    53 => [
    'codigo' => 'DOC-0054',
    'nombres' => 'MARINA',
    'apellidos' => 'URZAGASTE ALFARO',
    'especialidad' => 'LENGUA ESPAÑOLA',
    'numero_documento' => '90000054',
  ],
    54 => [
    'codigo' => 'DOC-0055',
    'nombres' => 'BERTHY',
    'apellidos' => 'VACA GOMEZ',
    'especialidad' => 'MATEMÁTICA',
    'numero_documento' => '90000055',
  ],
    55 => [
    'codigo' => 'DOC-0056',
    'nombres' => 'ROBERTO CARLOS',
    'apellidos' => 'RUIZ GAITE',
    'especialidad' => 'TECNOLOGÍA',
    'numero_documento' => '90000056',
  ],
  ];

        $niveles = ['Inicial en Familia Comunitaria' => 1, 'Primaria Comunitaria Vocacional' => 2, 'Secundaria Comunitaria Productiva' => 3];

        foreach ($docentes as $d) {
            DB::table('teachers')->updateOrInsert(
                ['codigo' => $d['codigo']],
                ['nombres' => $d['nombres'], 'apellidos' => $d['apellidos'], 'especialidad' => $d['especialidad'], 'numero_documento' => $d['numero_documento'], 'complemento' => '', 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Materias
        $materias = [
    0 => [
    'nombre' => 'Lenguaje y Comunicación',
    'level_id' => 1,
  ],
    1 => [
    'nombre' => 'Pensamiento Matemático',
    'level_id' => 1,
  ],
    2 => [
    'nombre' => 'Expresión Artística',
    'level_id' => 1,
  ],
    3 => [
    'nombre' => 'Ciencias de la Vida',
    'level_id' => 1,
  ],
    4 => [
    'nombre' => 'Educación Física',
    'level_id' => 1,
  ],
    5 => [
    'nombre' => 'Música y Ritmo',
    'level_id' => 1,
  ],
    6 => [
    'nombre' => 'Matemáticas',
    'level_id' => 2,
  ],
    7 => [
    'nombre' => 'Lenguaje',
    'level_id' => 2,
  ],
    8 => [
    'nombre' => 'Ciencias Naturales',
    'level_id' => 2,
  ],
    9 => [
    'nombre' => 'Ciencias Sociales',
    'level_id' => 2,
  ],
    10 => [
    'nombre' => 'Inglés',
    'level_id' => 2,
  ],
    11 => [
    'nombre' => 'Artes Plásticas',
    'level_id' => 2,
  ],
    12 => [
    'nombre' => 'Religión o Ética',
    'level_id' => 2,
  ],
    13 => [
    'nombre' => 'Física',
    'level_id' => 3,
  ],
    14 => [
    'nombre' => 'Química',
    'level_id' => 3,
  ],
    15 => [
    'nombre' => 'Biología',
    'level_id' => 3,
  ],
    16 => [
    'nombre' => 'Lenguaje y Literatura',
    'level_id' => 3,
  ],
    17 => [
    'nombre' => 'Filosofía',
    'level_id' => 3,
  ],
    18 => [
    'nombre' => 'Historia',
    'level_id' => 3,
  ],
    19 => [
    'nombre' => 'Psicología',
    'level_id' => 3,
  ],
    20 => [
    'nombre' => 'Informática',
    'level_id' => 3,
  ],
    21 => [
    'nombre' => 'Educación física.',
    'level_id' => 1,
  ],
    22 => [
    'nombre' => 'Comunicación y lenguaje',
    'level_id' => 1,
  ],
    23 => [
    'nombre' => 'Desarrollo bio-psico-social',
    'level_id' => 1,
  ],
    24 => [
    'nombre' => 'Desarrollo del conocimiento y la producción.',
    'level_id' => 1,
  ],
    25 => [
    'nombre' => 'Música',
    'level_id' => 1,
  ],
    26 => [
    'nombre' => 'TECNOLOGÍA',
    'level_id' => 1,
  ],
    27 => [
    'nombre' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'level_id' => 2,
  ],
    28 => [
    'nombre' => 'EDUCACIÓN MUSICAL',
    'level_id' => 2,
  ],
    29 => [
    'nombre' => 'ARTES PLÁSTICAS Y VISUALES',
    'level_id' => 2,
  ],
    30 => [
    'nombre' => 'COMUNICACIÓN Y LENGUAJES',
    'level_id' => 2,
  ],
    31 => [
    'nombre' => 'Lengua española',
    'level_id' => 2,
  ],
    32 => [
    'nombre' => 'MATEMÁTICA',
    'level_id' => 2,
  ],
    33 => [
    'nombre' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'level_id' => 2,
  ],
    34 => [
    'nombre' => 'Lengua extranjera',
    'level_id' => 2,
  ],
    35 => [
    'nombre' => 'COSMOVISIONES Y FILOSOFÍA',
    'level_id' => 3,
  ],
    36 => [
    'nombre' => 'INVESTIGACIÓN CIENTÍFICA',
    'level_id' => 3,
  ],
  ];
        $subjectIds = [];
        foreach ($materias as $m) {
            $sid = DB::table('subjects')->where('nombre', $m['nombre'])->value('id');
            if (!$sid) {
                $sid = DB::table('subjects')->insertGetId(['nombre' => $m['nombre'], 'descripcion' => null, 'level_id' => $m['level_id'], 'estado' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
            }
            $subjectIds[$m['nombre']] = $sid;
        }

        // Asignaciones curso -> materia -> docente
        $asignaciones = [
    0 => [
    'curso' => 'I1A',
    'materia' => 'Educación física.',
    'docente' => 'DOC-0002',
  ],
    1 => [
    'curso' => 'I1A',
    'materia' => 'Comunicación y lenguaje',
    'docente' => 'DOC-0003',
  ],
    2 => [
    'curso' => 'I1A',
    'materia' => 'Desarrollo bio-psico-social',
    'docente' => 'DOC-0003',
  ],
    3 => [
    'curso' => 'I1A',
    'materia' => 'Desarrollo del conocimiento y la producción.',
    'docente' => 'DOC-0003',
  ],
    4 => [
    'curso' => 'I1A',
    'materia' => 'Música',
    'docente' => 'DOC-0004',
  ],
    5 => [
    'curso' => 'I1A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    6 => [
    'curso' => 'I1B',
    'materia' => 'Educación física.',
    'docente' => 'DOC-0002',
  ],
    7 => [
    'curso' => 'I1B',
    'materia' => 'Música',
    'docente' => 'DOC-0004',
  ],
    8 => [
    'curso' => 'I1B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    9 => [
    'curso' => 'I1B',
    'materia' => 'Comunicación y lenguaje',
    'docente' => 'DOC-0006',
  ],
    10 => [
    'curso' => 'I1B',
    'materia' => 'Desarrollo bio-psico-social',
    'docente' => 'DOC-0006',
  ],
    11 => [
    'curso' => 'I1B',
    'materia' => 'Desarrollo del conocimiento y la producción.',
    'docente' => 'DOC-0006',
  ],
    12 => [
    'curso' => 'I2A',
    'materia' => 'Educación física.',
    'docente' => 'DOC-0002',
  ],
    13 => [
    'curso' => 'I2A',
    'materia' => 'Música',
    'docente' => 'DOC-0004',
  ],
    14 => [
    'curso' => 'I2A',
    'materia' => 'Comunicación y lenguaje',
    'docente' => 'DOC-0007',
  ],
    15 => [
    'curso' => 'I2A',
    'materia' => 'Desarrollo bio-psico-social',
    'docente' => 'DOC-0007',
  ],
    16 => [
    'curso' => 'I2A',
    'materia' => 'Desarrollo del conocimiento y la producción.',
    'docente' => 'DOC-0007',
  ],
    17 => [
    'curso' => 'I2A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    18 => [
    'curso' => 'I2B',
    'materia' => 'Educación física.',
    'docente' => 'DOC-0002',
  ],
    19 => [
    'curso' => 'I2B',
    'materia' => 'Música',
    'docente' => 'DOC-0004',
  ],
    20 => [
    'curso' => 'I2B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    21 => [
    'curso' => 'I2B',
    'materia' => 'Comunicación y lenguaje',
    'docente' => 'DOC-0009',
  ],
    22 => [
    'curso' => 'I2B',
    'materia' => 'Desarrollo bio-psico-social',
    'docente' => 'DOC-0009',
  ],
    23 => [
    'curso' => 'I2B',
    'materia' => 'Desarrollo del conocimiento y la producción.',
    'docente' => 'DOC-0009',
  ],
    24 => [
    'curso' => 'P1A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0002',
  ],
    25 => [
    'curso' => 'P1A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    26 => [
    'curso' => 'P1A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    27 => [
    'curso' => 'P1A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    28 => [
    'curso' => 'P1A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0011',
  ],
    29 => [
    'curso' => 'P1A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0011',
  ],
    30 => [
    'curso' => 'P1A',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0011',
  ],
    31 => [
    'curso' => 'P1A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0011',
  ],
    32 => [
    'curso' => 'P1A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0011',
  ],
    33 => [
    'curso' => 'P1A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0011',
  ],
    34 => [
    'curso' => 'P1A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    35 => [
    'curso' => 'P1B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0002',
  ],
    36 => [
    'curso' => 'P1B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    37 => [
    'curso' => 'P1B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    38 => [
    'curso' => 'P1B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0012',
  ],
    39 => [
    'curso' => 'P1B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0012',
  ],
    40 => [
    'curso' => 'P1B',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0012',
  ],
    41 => [
    'curso' => 'P1B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0012',
  ],
    42 => [
    'curso' => 'P1B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0012',
  ],
    43 => [
    'curso' => 'P1B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0012',
  ],
    44 => [
    'curso' => 'P1B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    45 => [
    'curso' => 'P1B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    46 => [
    'curso' => 'P1C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0002',
  ],
    47 => [
    'curso' => 'P1C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    48 => [
    'curso' => 'P1C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    49 => [
    'curso' => 'P1C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    50 => [
    'curso' => 'P1C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    51 => [
    'curso' => 'P1C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0013',
  ],
    52 => [
    'curso' => 'P1C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0013',
  ],
    53 => [
    'curso' => 'P1C',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0013',
  ],
    54 => [
    'curso' => 'P1C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0013',
  ],
    55 => [
    'curso' => 'P1C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0013',
  ],
    56 => [
    'curso' => 'P1C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0013',
  ],
    57 => [
    'curso' => 'P2A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    58 => [
    'curso' => 'P2A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0015',
  ],
    59 => [
    'curso' => 'P2A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0015',
  ],
    60 => [
    'curso' => 'P2A',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0015',
  ],
    61 => [
    'curso' => 'P2A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0015',
  ],
    62 => [
    'curso' => 'P2A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0015',
  ],
    63 => [
    'curso' => 'P2A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0015',
  ],
    64 => [
    'curso' => 'P2A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    65 => [
    'curso' => 'P2A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    66 => [
    'curso' => 'P2A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    67 => [
    'curso' => 'P2A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    68 => [
    'curso' => 'P2B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    69 => [
    'curso' => 'P2B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    70 => [
    'curso' => 'P2B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    71 => [
    'curso' => 'P2B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    72 => [
    'curso' => 'P2B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0017',
  ],
    73 => [
    'curso' => 'P2B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0017',
  ],
    74 => [
    'curso' => 'P2B',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0017',
  ],
    75 => [
    'curso' => 'P2B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0017',
  ],
    76 => [
    'curso' => 'P2B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0017',
  ],
    77 => [
    'curso' => 'P2B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0017',
  ],
    78 => [
    'curso' => 'P2B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    79 => [
    'curso' => 'P2C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    80 => [
    'curso' => 'P2C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    81 => [
    'curso' => 'P2C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0018',
  ],
    82 => [
    'curso' => 'P2C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0018',
  ],
    83 => [
    'curso' => 'P2C',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0018',
  ],
    84 => [
    'curso' => 'P2C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0018',
  ],
    85 => [
    'curso' => 'P2C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0018',
  ],
    86 => [
    'curso' => 'P2C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0018',
  ],
    87 => [
    'curso' => 'P2C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    88 => [
    'curso' => 'P2C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    89 => [
    'curso' => 'P2C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    90 => [
    'curso' => 'P3A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    91 => [
    'curso' => 'P3A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    92 => [
    'curso' => 'P3A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    93 => [
    'curso' => 'P3A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0019',
  ],
    94 => [
    'curso' => 'P3A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0019',
  ],
    95 => [
    'curso' => 'P3A',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0019',
  ],
    96 => [
    'curso' => 'P3A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0019',
  ],
    97 => [
    'curso' => 'P3A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0019',
  ],
    98 => [
    'curso' => 'P3A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0019',
  ],
    99 => [
    'curso' => 'P3A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    100 => [
    'curso' => 'P3A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    101 => [
    'curso' => 'P3B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    102 => [
    'curso' => 'P3B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    103 => [
    'curso' => 'P3B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0004',
  ],
    104 => [
    'curso' => 'P3B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0020',
  ],
    105 => [
    'curso' => 'P3B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0020',
  ],
    106 => [
    'curso' => 'P3B',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0020',
  ],
    107 => [
    'curso' => 'P3B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0020',
  ],
    108 => [
    'curso' => 'P3B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0020',
  ],
    109 => [
    'curso' => 'P3B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0020',
  ],
    110 => [
    'curso' => 'P3B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    111 => [
    'curso' => 'P3B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    112 => [
    'curso' => 'P3C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    113 => [
    'curso' => 'P3C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    114 => [
    'curso' => 'P3C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0022',
  ],
    115 => [
    'curso' => 'P3C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0022',
  ],
    116 => [
    'curso' => 'P3C',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0022',
  ],
    117 => [
    'curso' => 'P3C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0022',
  ],
    118 => [
    'curso' => 'P3C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0022',
  ],
    119 => [
    'curso' => 'P3C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0022',
  ],
    120 => [
    'curso' => 'P3C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    121 => [
    'curso' => 'P3C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0010',
  ],
    122 => [
    'curso' => 'P3C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0008',
  ],
    123 => [
    'curso' => 'P4A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    124 => [
    'curso' => 'P4A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    125 => [
    'curso' => 'P4A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    126 => [
    'curso' => 'P4A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    127 => [
    'curso' => 'P4A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    128 => [
    'curso' => 'P4A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0025',
  ],
    129 => [
    'curso' => 'P4A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0025',
  ],
    130 => [
    'curso' => 'P4A',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0025',
  ],
    131 => [
    'curso' => 'P4A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0025',
  ],
    132 => [
    'curso' => 'P4A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0025',
  ],
    133 => [
    'curso' => 'P4A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0025',
  ],
    134 => [
    'curso' => 'P4B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    135 => [
    'curso' => 'P4B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    136 => [
    'curso' => 'P4B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    137 => [
    'curso' => 'P4B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    138 => [
    'curso' => 'P4B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    139 => [
    'curso' => 'P4B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0026',
  ],
    140 => [
    'curso' => 'P4B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0026',
  ],
    141 => [
    'curso' => 'P4B',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0026',
  ],
    142 => [
    'curso' => 'P4B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0026',
  ],
    143 => [
    'curso' => 'P4B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0026',
  ],
    144 => [
    'curso' => 'P4B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0026',
  ],
    145 => [
    'curso' => 'P4C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    146 => [
    'curso' => 'P4C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    147 => [
    'curso' => 'P4C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    148 => [
    'curso' => 'P4C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    149 => [
    'curso' => 'P4C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    150 => [
    'curso' => 'P4C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0027',
  ],
    151 => [
    'curso' => 'P4C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0027',
  ],
    152 => [
    'curso' => 'P4C',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0027',
  ],
    153 => [
    'curso' => 'P4C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0027',
  ],
    154 => [
    'curso' => 'P4C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0027',
  ],
    155 => [
    'curso' => 'P4C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0027',
  ],
    156 => [
    'curso' => 'P5A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    157 => [
    'curso' => 'P5A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    158 => [
    'curso' => 'P5A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0028',
  ],
    159 => [
    'curso' => 'P5A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0028',
  ],
    160 => [
    'curso' => 'P5A',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0029',
  ],
    161 => [
    'curso' => 'P5A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0029',
  ],
    162 => [
    'curso' => 'P5A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    163 => [
    'curso' => 'P5A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0030',
  ],
    164 => [
    'curso' => 'P5A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    165 => [
    'curso' => 'P5A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    166 => [
    'curso' => 'P5A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0031',
  ],
    167 => [
    'curso' => 'P5B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    168 => [
    'curso' => 'P5B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    169 => [
    'curso' => 'P5B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0028',
  ],
    170 => [
    'curso' => 'P5B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0028',
  ],
    171 => [
    'curso' => 'P5B',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0029',
  ],
    172 => [
    'curso' => 'P5B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0029',
  ],
    173 => [
    'curso' => 'P5B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    174 => [
    'curso' => 'P5B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0030',
  ],
    175 => [
    'curso' => 'P5B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    176 => [
    'curso' => 'P5B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    177 => [
    'curso' => 'P5B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0031',
  ],
    178 => [
    'curso' => 'P5C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    179 => [
    'curso' => 'P5C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    180 => [
    'curso' => 'P5C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0028',
  ],
    181 => [
    'curso' => 'P5C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0028',
  ],
    182 => [
    'curso' => 'P5C',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0029',
  ],
    183 => [
    'curso' => 'P5C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0029',
  ],
    184 => [
    'curso' => 'P5C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    185 => [
    'curso' => 'P5C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0030',
  ],
    186 => [
    'curso' => 'P5C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    187 => [
    'curso' => 'P5C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    188 => [
    'curso' => 'P5C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0031',
  ],
    189 => [
    'curso' => 'P6A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    190 => [
    'curso' => 'P6A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    191 => [
    'curso' => 'P6A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0028',
  ],
    192 => [
    'curso' => 'P6A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0028',
  ],
    193 => [
    'curso' => 'P6A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0029',
  ],
    194 => [
    'curso' => 'P6A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    195 => [
    'curso' => 'P6A',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0032',
  ],
    196 => [
    'curso' => 'P6A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0032',
  ],
    197 => [
    'curso' => 'P6A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    198 => [
    'curso' => 'P6A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    199 => [
    'curso' => 'P6A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0031',
  ],
    200 => [
    'curso' => 'P6B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    201 => [
    'curso' => 'P6B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    202 => [
    'curso' => 'P6B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    203 => [
    'curso' => 'P6B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0028',
  ],
    204 => [
    'curso' => 'P6B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0028',
  ],
    205 => [
    'curso' => 'P6B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    206 => [
    'curso' => 'P6B',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0032',
  ],
    207 => [
    'curso' => 'P6B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0032',
  ],
    208 => [
    'curso' => 'P6B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0030',
  ],
    209 => [
    'curso' => 'P6B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    210 => [
    'curso' => 'P6B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    211 => [
    'curso' => 'P6B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0031',
  ],
    212 => [
    'curso' => 'P6C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0014',
  ],
    213 => [
    'curso' => 'P6C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0021',
  ],
    214 => [
    'curso' => 'P6C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0028',
  ],
    215 => [
    'curso' => 'P6C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0028',
  ],
    216 => [
    'curso' => 'P6C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0016',
  ],
    217 => [
    'curso' => 'P6C',
    'materia' => 'COMUNICACIÓN Y LENGUAJES',
    'docente' => 'DOC-0032',
  ],
    218 => [
    'curso' => 'P6C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0032',
  ],
    219 => [
    'curso' => 'P6C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0030',
  ],
    220 => [
    'curso' => 'P6C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    221 => [
    'curso' => 'P6C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0024',
  ],
    222 => [
    'curso' => 'P6C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0031',
  ],
    223 => [
    'curso' => 'S1A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    224 => [
    'curso' => 'S1A',
    'materia' => 'Psicología',
    'docente' => 'DOC-0028',
  ],
    225 => [
    'curso' => 'S1A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    226 => [
    'curso' => 'S1A',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    227 => [
    'curso' => 'S1A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    228 => [
    'curso' => 'S1A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    229 => [
    'curso' => 'S1A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    230 => [
    'curso' => 'S1A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0039',
  ],
    231 => [
    'curso' => 'S1A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    232 => [
    'curso' => 'S1A',
    'materia' => 'Química',
    'docente' => 'DOC-0040',
  ],
    233 => [
    'curso' => 'S1A',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    234 => [
    'curso' => 'S1A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0041',
  ],
    235 => [
    'curso' => 'S1A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    236 => [
    'curso' => 'S1A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0042',
  ],
    237 => [
    'curso' => 'S1B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    238 => [
    'curso' => 'S1B',
    'materia' => 'Psicología',
    'docente' => 'DOC-0028',
  ],
    239 => [
    'curso' => 'S1B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    240 => [
    'curso' => 'S1B',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    241 => [
    'curso' => 'S1B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    242 => [
    'curso' => 'S1B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    243 => [
    'curso' => 'S1B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    244 => [
    'curso' => 'S1B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0039',
  ],
    245 => [
    'curso' => 'S1B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    246 => [
    'curso' => 'S1B',
    'materia' => 'Química',
    'docente' => 'DOC-0040',
  ],
    247 => [
    'curso' => 'S1B',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    248 => [
    'curso' => 'S1B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0041',
  ],
    249 => [
    'curso' => 'S1B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    250 => [
    'curso' => 'S1B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0042',
  ],
    251 => [
    'curso' => 'S1C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    252 => [
    'curso' => 'S1C',
    'materia' => 'Psicología',
    'docente' => 'DOC-0028',
  ],
    253 => [
    'curso' => 'S1C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    254 => [
    'curso' => 'S1C',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    255 => [
    'curso' => 'S1C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    256 => [
    'curso' => 'S1C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    257 => [
    'curso' => 'S1C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    258 => [
    'curso' => 'S1C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0039',
  ],
    259 => [
    'curso' => 'S1C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    260 => [
    'curso' => 'S1C',
    'materia' => 'Química',
    'docente' => 'DOC-0040',
  ],
    261 => [
    'curso' => 'S1C',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    262 => [
    'curso' => 'S1C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0041',
  ],
    263 => [
    'curso' => 'S1C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    264 => [
    'curso' => 'S1C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0042',
  ],
    265 => [
    'curso' => 'S2A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    266 => [
    'curso' => 'S2A',
    'materia' => 'Psicología',
    'docente' => 'DOC-0028',
  ],
    267 => [
    'curso' => 'S2A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    268 => [
    'curso' => 'S2A',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    269 => [
    'curso' => 'S2A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0043',
  ],
    270 => [
    'curso' => 'S2A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    271 => [
    'curso' => 'S2A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    272 => [
    'curso' => 'S2A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    273 => [
    'curso' => 'S2A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    274 => [
    'curso' => 'S2A',
    'materia' => 'Química',
    'docente' => 'DOC-0040',
  ],
    275 => [
    'curso' => 'S2A',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    276 => [
    'curso' => 'S2A',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0041',
  ],
    277 => [
    'curso' => 'S2A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    278 => [
    'curso' => 'S2A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0042',
  ],
    279 => [
    'curso' => 'S2B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    280 => [
    'curso' => 'S2B',
    'materia' => 'Psicología',
    'docente' => 'DOC-0028',
  ],
    281 => [
    'curso' => 'S2B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    282 => [
    'curso' => 'S2B',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    283 => [
    'curso' => 'S2B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    284 => [
    'curso' => 'S2B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    285 => [
    'curso' => 'S2B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    286 => [
    'curso' => 'S2B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0039',
  ],
    287 => [
    'curso' => 'S2B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    288 => [
    'curso' => 'S2B',
    'materia' => 'Química',
    'docente' => 'DOC-0040',
  ],
    289 => [
    'curso' => 'S2B',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    290 => [
    'curso' => 'S2B',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0041',
  ],
    291 => [
    'curso' => 'S2B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    292 => [
    'curso' => 'S2B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0042',
  ],
    293 => [
    'curso' => 'S2C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    294 => [
    'curso' => 'S2C',
    'materia' => 'Psicología',
    'docente' => 'DOC-0028',
  ],
    295 => [
    'curso' => 'S2C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    296 => [
    'curso' => 'S2C',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    297 => [
    'curso' => 'S2C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0043',
  ],
    298 => [
    'curso' => 'S2C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    299 => [
    'curso' => 'S2C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    300 => [
    'curso' => 'S2C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    301 => [
    'curso' => 'S2C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    302 => [
    'curso' => 'S2C',
    'materia' => 'Química',
    'docente' => 'DOC-0040',
  ],
    303 => [
    'curso' => 'S2C',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    304 => [
    'curso' => 'S2C',
    'materia' => 'Ciencias Naturales',
    'docente' => 'DOC-0041',
  ],
    305 => [
    'curso' => 'S2C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    306 => [
    'curso' => 'S2C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0042',
  ],
    307 => [
    'curso' => 'S3A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    308 => [
    'curso' => 'S3A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    309 => [
    'curso' => 'S3A',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    310 => [
    'curso' => 'S3A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    311 => [
    'curso' => 'S3A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    312 => [
    'curso' => 'S3A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    313 => [
    'curso' => 'S3A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    314 => [
    'curso' => 'S3A',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    315 => [
    'curso' => 'S3A',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    316 => [
    'curso' => 'S3A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    317 => [
    'curso' => 'S3A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    318 => [
    'curso' => 'S3A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0046',
  ],
    319 => [
    'curso' => 'S3A',
    'materia' => 'Química',
    'docente' => 'DOC-0047',
  ],
    320 => [
    'curso' => 'S3B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    321 => [
    'curso' => 'S3B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    322 => [
    'curso' => 'S3B',
    'materia' => 'Física',
    'docente' => 'DOC-0035',
  ],
    323 => [
    'curso' => 'S3B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    324 => [
    'curso' => 'S3B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    325 => [
    'curso' => 'S3B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    326 => [
    'curso' => 'S3B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    327 => [
    'curso' => 'S3B',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    328 => [
    'curso' => 'S3B',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    329 => [
    'curso' => 'S3B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    330 => [
    'curso' => 'S3B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    331 => [
    'curso' => 'S3B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0046',
  ],
    332 => [
    'curso' => 'S3B',
    'materia' => 'Química',
    'docente' => 'DOC-0047',
  ],
    333 => [
    'curso' => 'S3C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0033',
  ],
    334 => [
    'curso' => 'S3C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    335 => [
    'curso' => 'S3C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0036',
  ],
    336 => [
    'curso' => 'S3C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    337 => [
    'curso' => 'S3C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    338 => [
    'curso' => 'S3C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    339 => [
    'curso' => 'S3C',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    340 => [
    'curso' => 'S3C',
    'materia' => 'Física',
    'docente' => 'DOC-0049',
  ],
    341 => [
    'curso' => 'S3C',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    342 => [
    'curso' => 'S3C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0023',
  ],
    343 => [
    'curso' => 'S3C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    344 => [
    'curso' => 'S3C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0046',
  ],
    345 => [
    'curso' => 'S3C',
    'materia' => 'Química',
    'docente' => 'DOC-0047',
  ],
    346 => [
    'curso' => 'S4A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    347 => [
    'curso' => 'S4A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    348 => [
    'curso' => 'S4A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0043',
  ],
    349 => [
    'curso' => 'S4A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    350 => [
    'curso' => 'S4A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    351 => [
    'curso' => 'S4A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    352 => [
    'curso' => 'S4A',
    'materia' => 'Física',
    'docente' => 'DOC-0049',
  ],
    353 => [
    'curso' => 'S4A',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    354 => [
    'curso' => 'S4A',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    355 => [
    'curso' => 'S4A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    356 => [
    'curso' => 'S4A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    357 => [
    'curso' => 'S4A',
    'materia' => 'Química',
    'docente' => 'DOC-0047',
  ],
    358 => [
    'curso' => 'S4A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    359 => [
    'curso' => 'S4B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    360 => [
    'curso' => 'S4B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    361 => [
    'curso' => 'S4B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0043',
  ],
    362 => [
    'curso' => 'S4B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    363 => [
    'curso' => 'S4B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    364 => [
    'curso' => 'S4B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    365 => [
    'curso' => 'S4B',
    'materia' => 'Física',
    'docente' => 'DOC-0049',
  ],
    366 => [
    'curso' => 'S4B',
    'materia' => 'Biología',
    'docente' => 'DOC-0041',
  ],
    367 => [
    'curso' => 'S4B',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    368 => [
    'curso' => 'S4B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    369 => [
    'curso' => 'S4B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    370 => [
    'curso' => 'S4B',
    'materia' => 'Química',
    'docente' => 'DOC-0047',
  ],
    371 => [
    'curso' => 'S4B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    372 => [
    'curso' => 'S4C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    373 => [
    'curso' => 'S4C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    374 => [
    'curso' => 'S4C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0043',
  ],
    375 => [
    'curso' => 'S4C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    376 => [
    'curso' => 'S4C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    377 => [
    'curso' => 'S4C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    378 => [
    'curso' => 'S4C',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    379 => [
    'curso' => 'S4C',
    'materia' => 'Física',
    'docente' => 'DOC-0049',
  ],
    380 => [
    'curso' => 'S4C',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    381 => [
    'curso' => 'S4C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    382 => [
    'curso' => 'S4C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    383 => [
    'curso' => 'S4C',
    'materia' => 'Química',
    'docente' => 'DOC-0047',
  ],
    384 => [
    'curso' => 'S4C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    385 => [
    'curso' => 'S5A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    386 => [
    'curso' => 'S5A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    387 => [
    'curso' => 'S5A',
    'materia' => 'Física',
    'docente' => 'DOC-0052',
  ],
    388 => [
    'curso' => 'S5A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    389 => [
    'curso' => 'S5A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    390 => [
    'curso' => 'S5A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    391 => [
    'curso' => 'S5A',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    392 => [
    'curso' => 'S5A',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    393 => [
    'curso' => 'S5A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0045',
  ],
    394 => [
    'curso' => 'S5A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0046',
  ],
    395 => [
    'curso' => 'S5A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    396 => [
    'curso' => 'S5A',
    'materia' => 'Química',
    'docente' => 'DOC-0053',
  ],
    397 => [
    'curso' => 'S5A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    398 => [
    'curso' => 'S5B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    399 => [
    'curso' => 'S5B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    400 => [
    'curso' => 'S5B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    401 => [
    'curso' => 'S5B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    402 => [
    'curso' => 'S5B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    403 => [
    'curso' => 'S5B',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    404 => [
    'curso' => 'S5B',
    'materia' => 'Física',
    'docente' => 'DOC-0049',
  ],
    405 => [
    'curso' => 'S5B',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    406 => [
    'curso' => 'S5B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0046',
  ],
    407 => [
    'curso' => 'S5B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    408 => [
    'curso' => 'S5B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0054',
  ],
    409 => [
    'curso' => 'S5B',
    'materia' => 'Química',
    'docente' => 'DOC-0053',
  ],
    410 => [
    'curso' => 'S5B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    411 => [
    'curso' => 'S5C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    412 => [
    'curso' => 'S5C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    413 => [
    'curso' => 'S5C',
    'materia' => 'Física',
    'docente' => 'DOC-0052',
  ],
    414 => [
    'curso' => 'S5C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    415 => [
    'curso' => 'S5C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    416 => [
    'curso' => 'S5C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0005',
  ],
    417 => [
    'curso' => 'S5C',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    418 => [
    'curso' => 'S5C',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    419 => [
    'curso' => 'S5C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    420 => [
    'curso' => 'S5C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0054',
  ],
    421 => [
    'curso' => 'S5C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0055',
  ],
    422 => [
    'curso' => 'S5C',
    'materia' => 'Química',
    'docente' => 'DOC-0053',
  ],
    423 => [
    'curso' => 'S5C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    424 => [
    'curso' => 'S6A',
    'materia' => 'INVESTIGACIÓN CIENTÍFICA',
    'docente' => 'DOC-0033',
  ],
    425 => [
    'curso' => 'S6A',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    426 => [
    'curso' => 'S6A',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    427 => [
    'curso' => 'S6A',
    'materia' => 'Física',
    'docente' => 'DOC-0052',
  ],
    428 => [
    'curso' => 'S6A',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    429 => [
    'curso' => 'S6A',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    430 => [
    'curso' => 'S6A',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    431 => [
    'curso' => 'S6A',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0056',
  ],
    432 => [
    'curso' => 'S6A',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    433 => [
    'curso' => 'S6A',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0054',
  ],
    434 => [
    'curso' => 'S6A',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0055',
  ],
    435 => [
    'curso' => 'S6A',
    'materia' => 'Química',
    'docente' => 'DOC-0053',
  ],
    436 => [
    'curso' => 'S6A',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    437 => [
    'curso' => 'S6B',
    'materia' => 'INVESTIGACIÓN CIENTÍFICA',
    'docente' => 'DOC-0033',
  ],
    438 => [
    'curso' => 'S6B',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    439 => [
    'curso' => 'S6B',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    440 => [
    'curso' => 'S6B',
    'materia' => 'Física',
    'docente' => 'DOC-0052',
  ],
    441 => [
    'curso' => 'S6B',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    442 => [
    'curso' => 'S6B',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    443 => [
    'curso' => 'S6B',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    444 => [
    'curso' => 'S6B',
    'materia' => 'COSMOVISIONES Y FILOSOFÍA',
    'docente' => 'DOC-0044',
  ],
    445 => [
    'curso' => 'S6B',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0056',
  ],
    446 => [
    'curso' => 'S6B',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    447 => [
    'curso' => 'S6B',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0054',
  ],
    448 => [
    'curso' => 'S6B',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0055',
  ],
    449 => [
    'curso' => 'S6B',
    'materia' => 'Química',
    'docente' => 'DOC-0053',
  ],
    450 => [
    'curso' => 'S6B',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    451 => [
    'curso' => 'S6C',
    'materia' => 'INVESTIGACIÓN CIENTÍFICA',
    'docente' => 'DOC-0033',
  ],
    452 => [
    'curso' => 'S6C',
    'materia' => 'Lengua extranjera',
    'docente' => 'DOC-0001',
  ],
    453 => [
    'curso' => 'S6C',
    'materia' => 'EDUCACIÓN FÍSICA Y DEPORTES',
    'docente' => 'DOC-0034',
  ],
    454 => [
    'curso' => 'S6C',
    'materia' => 'Física',
    'docente' => 'DOC-0052',
  ],
    455 => [
    'curso' => 'S6C',
    'materia' => 'VALORES, ESPIRITUALIDADES Y RELIGIONES',
    'docente' => 'DOC-0037',
  ],
    456 => [
    'curso' => 'S6C',
    'materia' => 'EDUCACIÓN MUSICAL',
    'docente' => 'DOC-0038',
  ],
    457 => [
    'curso' => 'S6C',
    'materia' => 'Biología',
    'docente' => 'DOC-0048',
  ],
    458 => [
    'curso' => 'S6C',
    'materia' => 'TECNOLOGÍA',
    'docente' => 'DOC-0056',
  ],
    459 => [
    'curso' => 'S6C',
    'materia' => 'Ciencias Sociales',
    'docente' => 'DOC-0050',
  ],
    460 => [
    'curso' => 'S6C',
    'materia' => 'Lengua española',
    'docente' => 'DOC-0054',
  ],
    461 => [
    'curso' => 'S6C',
    'materia' => 'MATEMÁTICA',
    'docente' => 'DOC-0055',
  ],
    462 => [
    'curso' => 'S6C',
    'materia' => 'Química',
    'docente' => 'DOC-0053',
  ],
    463 => [
    'curso' => 'S6C',
    'materia' => 'ARTES PLÁSTICAS Y VISUALES',
    'docente' => 'DOC-0051',
  ],
    464 => [
    'curso' => 'I1A',
    'materia' => 'Inglés',
    'docente' => 'DOC-0001',
  ],
    465 => [
    'curso' => 'I1B',
    'materia' => 'Inglés',
    'docente' => 'DOC-0001',
  ],
    466 => [
    'curso' => 'I2A',
    'materia' => 'Inglés',
    'docente' => 'DOC-0008',
  ],
    467 => [
    'curso' => 'I2B',
    'materia' => 'Inglés',
    'docente' => 'DOC-0008',
  ],
  ];
        $año = DB::table('academic_years')->where('estado', 'ACTIVA')->value('id') ?? DB::table('academic_years')->value('id');
        foreach ($asignaciones as $a) {
            $sec = DB::table('sections')->where('codigo', $a['curso'])->first();
            $teacher = DB::table('teachers')->where('codigo', $a['docente'])->first();
            if (!$sec || !$teacher || !isset($subjectIds[$a['materia']])) continue;
            $course = DB::table('courses')->where('section_id', $sec->id)->where('academic_year_id', $año)->first();
            if (!$course) continue;
            $exists = DB::table('course_subjects')->where('course_id', $course->id)->where('subject_id', $subjectIds[$a['materia']])->where('teacher_id', $teacher->id)->exists();
            if (!$exists) {
                DB::table('course_subjects')->insert(['course_id' => $course->id, 'subject_id' => $subjectIds[$a['materia']], 'teacher_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $this->command->info('Docentes 2026 y asignaciones importadas.');
    }
}
