<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * HU-59: materias oficiales por nivel y curso (listas NIFC / PVC / SCP).
 *
 * - Enriquece las materias existentes (match por nombre normalizado dentro del
 *   nivel) con código, sigla, área y grupo; crea las faltantes.
 * - No toca asignaciones (course_subjects) ni desactiva nada.
 * - Idempotente: se puede correr varias veces.
 * - Misma sigla en varios grupos -> una fila canónica; `grupo` acumula las
 *   listas donde aplica y `codigo` conserva el del primer grupo.
 */
class MateriasOficialesSeeder extends Seeder
{
    public function run(): void
    {
        $inicial = $this->nivel('Inicial en Familia Comunitaria', 1);
        $primaria = $this->nivel('Primaria Comunitaria Vocacional', 2);
        $secundaria = $this->nivel('Secundaria Comunitaria Productiva', 3);

        if (! $inicial || ! $primaria || ! $secundaria) {
            return;
        }

        $pvc = 'PVCP1,PVCP2-P4,PCPP5,PCPP6';
        $scpAlto = 'SCPS3-5,SCPS6ABC';

        $catalogo = [
            // NIFC — Inicial en Familia Comunitaria (cualitativo)
            [$inicial->id, '01', 'Desarrollo del conocimiento y la producción.', 'DCP', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN', 'NIFC'],
            [$inicial->id, '02', 'Comunicación y lenguaje', 'CL', 'COMUNIDAD Y SOCIEDAD', 'NIFC'],
            [$inicial->id, '10', 'Desarrollo bio-psico-social', 'DBS', 'VIDA TIERRA TERRITORIO', 'NIFC'],
            [$inicial->id, '04', 'Desarrollo sociocultural, afectivo y espiritual', 'DSAE', 'COSMOS Y PENSAMIENTO', 'NIFC'],
            [$inicial->id, '06', 'Educación física.', 'EF', 'DESARROLLO CORPORAL, MOTRIZ Y MUSICAL', 'NIFC'],
            [$inicial->id, '07', 'Música', 'MUS', 'DESARROLLO CORPORAL, MOTRIZ Y MUSICAL', 'NIFC'],
            [$inicial->id, '08', 'TECNOLOGÍA', 'TEC', 'EDUCACIÓN TECNOLÓGICA', 'NIFC'],
            [$inicial->id, '09', 'INGLÉS', 'ING', 'EDUCACIÓN EN IDIOMAS', 'NIFC'],

            // PVC — Primaria (P1, P2-P4, P5, P6 comparten la lista)
            [$primaria->id, '01', 'COMUNICACIÓN Y LENGUAJES', 'CL', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '01.01', 'Lengua española', 'LELO', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '01.02', 'Lengua extranjera', 'LE', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '02', 'CIENCIAS SOCIALES', 'SOC', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '03', 'EDUCACIÓN FÍSICA Y DEPORTES', 'EFD', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '04', 'EDUCACIÓN MUSICAL', 'EM', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '05', 'ARTES PLÁSTICAS Y VISUALES', 'APLASVI', 'COMUNIDAD Y SOCIEDAD', $pvc],
            [$primaria->id, '06', 'MATEMÁTICA', 'MAT', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN', $pvc],
            [$primaria->id, '07', 'TECNOLOGÍA', 'TEC', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN', $pvc],
            [$primaria->id, '08', 'CIENCIAS NATURALES', 'CN', 'VIDA TIERRA TERRITORIO', $pvc],
            [$primaria->id, '09', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'VER', 'COSMOS Y PENSAMIENTO', $pvc],

            // SCP S1-S2 — Secundaria
            [$secundaria->id, '01', 'LENGUA ESPAÑOLA', 'LELO', 'COMUNIDAD Y SOCIEDAD', 'SCPS1-2'],
            [$secundaria->id, '02', 'LENGUA EXTRANJERA', 'LE', 'COMUNIDAD Y SOCIEDAD', 'SCPS1-2'],
            [$secundaria->id, '03', 'CIENCIAS SOCIALES', 'SOC', 'COMUNIDAD Y SOCIEDAD', 'SCPS1-2'],
            [$secundaria->id, '04', 'EDUCACIÓN FÍSICA Y DEPORTES', 'EFD', 'COMUNIDAD Y SOCIEDAD', 'SCPS1-2'],
            [$secundaria->id, '05', 'EDUCACIÓN MUSICAL', 'EM', 'COMUNIDAD Y SOCIEDAD', 'SCPS1-2'],
            [$secundaria->id, '06', 'ARTES PLÁSTICAS Y VISUALES', 'APLASVI', 'COMUNIDAD Y SOCIEDAD', 'SCPS1-2'],
            [$secundaria->id, '07', 'MATEMÁTICA', 'MAT', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN', 'SCPS1-2'],
            [$secundaria->id, '08', 'TECNOLOGÍA', 'TEC', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN', 'SCPS1-2'],
            [$secundaria->id, '09', 'CIENCIAS NATURALES', 'CN', 'VIDA TIERRA TERRITORIO', 'SCPS1-2'],
            [$secundaria->id, '09.1', 'BIOLOGIA', 'BIO', 'VIDA TIERRA TERRITORIO', 'SCPS1-2'],
            [$secundaria->id, '09.2', 'QUÍMICA', 'QUIM', 'VIDA TIERRA TERRITORIO', 'SCPS1-2'],
            [$secundaria->id, '09.3', 'FÍSICA', 'FIS', 'VIDA TIERRA TERRITORIO', 'SCPS1-2'],
            [$secundaria->id, '10', 'PSICOLOGÍA', 'PS', 'COSMOS Y PENSAMIENTO', 'SCPS1-2'],
            [$secundaria->id, '11', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'VER', 'COSMOS Y PENSAMIENTO', 'SCPS1-2'],

            // SCP S3-S5 y S6 — Secundaria (INV/EMP solo S6)
            [$secundaria->id, '01', 'LENGUA ESPAÑOLA', 'LELO', 'COMUNIDAD Y SOCIEDAD', $scpAlto],
            [$secundaria->id, '02', 'LENGUA EXTRANJERA', 'LE', 'COMUNIDAD Y SOCIEDAD', $scpAlto],
            [$secundaria->id, '03', 'CIENCIAS SOCIALES', 'SOC', 'COMUNIDAD Y SOCIEDAD', $scpAlto],
            [$secundaria->id, '04', 'EDUCACIÓN FÍSICA Y DEPORTES', 'EFD', 'COMUNIDAD Y SOCIEDAD', $scpAlto],
            [$secundaria->id, '05', 'EDUCACIÓN MUSICAL', 'EM', 'COMUNIDAD Y SOCIEDAD', $scpAlto],
            [$secundaria->id, '06', 'ARTES PLÁSTICAS Y VISUALES', 'APLASVI', 'COMUNIDAD Y SOCIEDAD', $scpAlto],
            [$secundaria->id, '07', 'MATEMÁTICA', 'MAT', 'CIENCIA, TECNOLOGÍA Y PRODUCCIÓN', $scpAlto],
            [$secundaria->id, '08', 'BIOLOGIA', 'BIO', 'VIDA TIERRA TERRITORIO', $scpAlto],
            [$secundaria->id, '09', 'FISICA', 'FIS', 'VIDA TIERRA TERRITORIO', $scpAlto],
            [$secundaria->id, '10', 'QUIMICA', 'QUIM', 'VIDA TIERRA TERRITORIO', $scpAlto],
            [$secundaria->id, '11', 'COSMOVISIONES Y FILOSOFÍA', 'CFIL', 'COSMOS Y PENSAMIENTO', $scpAlto],
            [$secundaria->id, '12', 'VALORES, ESPIRITUALIDADES Y RELIGIONES', 'VER', 'COSMOS Y PENSAMIENTO', $scpAlto],
            [$secundaria->id, '13', 'TECNOLOGÍA', 'TEC', 'EDUCACIÓN TECNOLÓGICA', $scpAlto],
            [$secundaria->id, '14', 'INVESTIGACIÓN CIENTÍFICA', 'INV', 'COMPLEMENTARIAS', 'SCPS6ABC'],
            [$secundaria->id, '15', 'EMPRENDIMIENTO', 'EMP', 'COMPLEMENTARIAS', 'SCPS6ABC'],
        ];

        foreach ($catalogo as [$levelId, $codigo, $nombre, $sigla, $area, $grupo]) {
            $this->aplicar($levelId, $codigo, $nombre, $sigla, $area, $grupo);
        }
    }

    private function nivel(string $nombre, int $orden): ?Level
    {
        return Level::where('nombre', $nombre)->first()
            ?? Level::where('orden', $orden)->first();
    }

    private function normalizar(string $texto): string
    {
        $texto = trim($texto);
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
        $texto = (string) preg_replace('/\s+/', ' ', $texto);

        return $texto;
    }

    /**
     * Busca la materia por sigla (canónica) o por nombre normalizado dentro del
     * nivel; si existe la enriquece, si no la crea. Acumula `grupo`.
     */
    private function aplicar(int $levelId, string $codigo, string $nombre, string $sigla, string $area, string $grupo): void
    {
        $existente = Subject::where('level_id', $levelId)
            ->where('sigla', $sigla)
            ->first();

        if (! $existente) {
            $norma = $this->normalizar($nombre);
            $existente = Subject::where('level_id', $levelId)->get()
                ->first(fn ($s) => $this->normalizar($s->nombre) === $norma);
        }

        if ($existente) {
            $grupos = array_filter(array_unique(array_merge(
                explode(',', (string) $existente->grupo),
                explode(',', $grupo)
            )));
            $existente->update([
                'nombre' => $nombre,
                'codigo' => $existente->codigo ?? $codigo,
                'sigla' => $sigla,
                'area' => $area,
                'grupo' => implode(',', $grupos),
            ]);

            return;
        }

        Subject::create([
            'nombre' => $nombre,
            'codigo' => $codigo,
            'sigla' => $sigla,
            'area' => $area,
            'grupo' => $grupo,
            'level_id' => $levelId,
            'estado' => 'ACTIVO',
        ]);
    }
}
