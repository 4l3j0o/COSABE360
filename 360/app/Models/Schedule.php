<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    public const DIAS_LABEL = [
        'LUNES' => 'Lunes',
        'MARTES' => 'Martes',
        'MIERCOLES' => 'Miércoles',
        'JUEVES' => 'Jueves',
        'VIERNES' => 'Viernes',
        'SABADO' => 'Sábado',
    ];

    /**
     * Periodos oficiales por nivel (fuente: Docs/Horarios/HORARIO_PERIODOS.html).
     * Se usan para la grilla completa del horario docente y la carga inicial.
     */
    public const PERIODOS_NIVEL = [
        'INICIAL' => [
            ['08:00', '08:35'], ['08:35', '09:10'], ['09:40', '10:15'],
            ['10:15', '10:50'], ['11:00', '11:30'], ['11:30', '12:00'],
        ],
        'PRIMARIA' => [
            ['07:30', '08:15'], ['08:15', '09:00'], ['09:15', '09:55'],
            ['09:55', '10:40'], ['10:55', '11:40'], ['11:40', '12:20'],
        ],
        'SECUNDARIA' => [
            ['14:00', '14:40'], ['14:40', '15:20'], ['15:30', '16:10'],
            ['16:10', '16:50'], ['17:05', '17:45'], ['17:45', '18:20'], ['18:20', '18:50'],
        ],
    ];

    /** Colores de la paleta del sistema por nivel. */
    public const ACCENT_NIVEL = [
        'inicial' => ['thead' => 'bg-primary-800', 'chip' => 'bg-primary-50 text-primary-800'],
        'primaria' => ['thead' => 'bg-info', 'chip' => 'bg-info/15 text-info'],
        'secundaria' => ['thead' => 'bg-secondary', 'chip' => 'bg-secondary/15 text-secondary'],
    ];

    /**
     * Nivel al que pertenece una franja horaria (por intersección con los
     * periodos oficiales). Null si no cruza ningún periodo.
     */
    public static function nivelDeFranja(string $inicio, string $fin): ?string
    {
        // 1) Coincidencia exacta con un periodo oficial
        foreach (self::PERIODOS_NIVEL as $nivel => $periodos) {
            foreach ($periodos as [$pi, $pf]) {
                if ($inicio === $pi && $fin === $pf) {
                    return $nivel;
                }
            }
        }
        // 2) Un solo nivel traslapado
        $cruzan = self::nivelesDeFranja($inicio, $fin);
        if (count($cruzan) === 1) {
            return $cruzan[0];
        }

        return null;
    }

    /** Todos los niveles que cruzan la franja. */
    public static function nivelesDeFranja(string $inicio, string $fin): array
    {
        $out = [];
        foreach (self::PERIODOS_NIVEL as $nivel => $periodos) {
            foreach ($periodos as [$pi, $pf]) {
                if ($inicio < $pf && $pi < $fin) {
                    $out[] = $nivel;
                    break;
                }
            }
        }

        return $out;
    }

    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'tipo',
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula',
        'estado',
    ];

    public const TIPOS = ['CLASE', 'VISITA'];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i:s',
        'hora_fin' => 'datetime:H:i:s',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
