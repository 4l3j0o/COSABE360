<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRudeRecord extends Model
{
    public const ESTADOS = ['PENDIENTE', 'ENVIADO', 'APROBADO', 'RECHAZADO'];
    public const TURNOS = ['MANANA', 'TARDE', 'NOCTURNO'];
    public const ZONAS = ['URBANA', 'RURAL', 'PERIFERIA'];

    protected $fillable = [
        'student_id',
        'codigo_rude',
        'codigo_sie_ue',
        'turno',
        'transporte',
        'zona_residencial',
        'procedencia_ie',
        'nuevo_ingreso',
        'idioma_originario',
        'salud_discapacidad',
        'oficialia_nacimiento',
        'libro_nacimiento',
        'partida_nacimiento',
        'folio_nacimiento',
        'anio_nacimiento',
        'estado',
        'fecha_envio',
        'fecha_respuesta',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'transporte' => 'boolean',
            'nuevo_ingreso' => 'boolean',
            'fecha_envio' => 'date',
            'fecha_respuesta' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'PENDIENTE';
    }

    public function estaEnviado(): bool
    {
        return $this->estado === 'ENVIADO';
    }

    /**
     * Valida CI boliviano: 7-8 dígitos, complemento opcional 1-2 caracteres alfanum.
     * Retorna true si es válido.
     */
    public static function validarCI(string $numero, ?string $complemento = null): bool
    {
        if (!preg_match('/^\d{7,8}$/', $numero)) {
            return false;
        }
        if ($complemento !== null && $complemento !== '' && !preg_match('/^[A-Za-z0-9]{1,5}$/', $complemento)) {
            return false;
        }
        return true;
    }

    /**
     * Valida certificado de nacimiento: si uno de los campos está presente, todos deben estar.
     */
    public static function validarCertificado(array $datos): bool
    {
        $campos = ['oficialia_nacimiento', 'libro_nacimiento', 'partida_nacimiento', 'folio_nacimiento', 'anio_nacimiento'];
        $presentes = array_filter($datos, fn ($v) => $v !== null && $v !== '');
        $count = count(array_intersect_key(array_flip($campos), $datos)) > 0 ? count(array_filter(array_intersect_key($datos, array_flip($campos)), fn ($v) => $v !== null && $v !== '')) : 0;

        // Si ninguno está, es válido (no obligatorio)
        // Si alguno está, todos deben estar
        $filled = 0;
        foreach ($campos as $c) {
            if (!empty($datos[$c])) {
                $filled++;
            }
        }
        if ($filled === 0) {
            return true;
        }
        return $filled === count($campos);
    }

    /**
     * Valida código SIE UE: 8 dígitos (configurable, por defecto 8).
     */
    public static function validarCodigoSIE(string $codigo): bool
    {
        return preg_match('/^\d{8}$/', $codigo) === 1;
    }
}
