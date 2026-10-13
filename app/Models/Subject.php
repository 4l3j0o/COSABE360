<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'sigla',
        'area',
        'grupo',
        'descripcion',
        'level_id',
        'horas_semanales',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'horas_semanales' => 'integer',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'subject_id');
    }

    public function sectionSubjects(): HasMany
    {
        return $this->hasMany(SectionSubject::class, 'subject_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'subject_id');
    }

    /**
     * HU-59: mapa legacy (orden de nivel => [nombre normalizado => sigla]).
     * Nombres del plan anterior / Excel que hoy equivalen a la sigla oficial.
     */
    public const LEGACY_SIGLAS = [
        2 => [
            'LENGUA ESPANOLA' => 'CL',
            'LENGUA EXTRANJERA' => 'CL',
            'COMUNICACION Y LENGUAJES' => 'CL',
            'TECNOLOGIA' => 'TEC',
            'VALORES, ESPIRITUALIDADES Y RELIGIONES' => 'VER',
        ],
        3 => [
            'LENGUA ESPANOLA' => 'LELO',
            'TECNOLOGIA' => 'TEC',
            'VALORES, ESPIRITUALIDADES Y RELIGIONES' => 'VER',
            'BIOLOGIA' => 'BIO',
            'FISICA' => 'FIS',
            'QUIMICA' => 'QUIM',
            'PSICOLOGIA' => 'CFIL',
            'COSMOVISIONES Y FILOSOFIA' => 'CFIL',
        ],
    ];

    public static function normalizar(string $texto): string
    {
        $texto = trim($texto);
        $texto = mb_strtoupper($texto, 'UTF-8');
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);

        return (string) preg_replace('/\s+/', ' ', $texto);
    }

    /**
     * HU-59: resuelve una materia por nombre dentro de un nivel:
     * coincidencia exacta normalizada, si no mapa legacy -> sigla oficial.
     */
    public static function resolver(int $levelId, string $nombre): ?Subject
    {
        $norma = self::normalizar($nombre);

        $exacta = self::where('level_id', $levelId)->get()
            ->first(fn ($s) => self::normalizar($s->nombre) === $norma);

        if ($exacta) {
            return $exacta;
        }

        $orden = Level::find($levelId)?->orden;
        $sigla = $orden ? (self::LEGACY_SIGLAS[$orden][$norma] ?? null) : null;

        if (! $sigla) {
            return null;
        }

        return self::where('level_id', $levelId)->where('sigla', $sigla)->first();
    }
}
