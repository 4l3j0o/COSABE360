<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodigoReserva extends Model
{
    public const MINUTOS_VIGENCIA = 30;

    protected $fillable = [
        'codigo',
        'user_id',
        'expira_en',
        'estado',
    ];

    protected function casts(): array
    {
        return ['expira_en' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vigente(): bool
    {
        return $this->estado === 'RESERVADO' && $this->expira_en->isFuture();
    }

    /**
     * Códigos ocupados ahora: usados + reservados vigentes.
     */
    public static function codigosOcupados(): array
    {
        $usados = Student::pluck('codigo')->map(fn ($c) => trim((string) $c))->all();
        $reservados = self::where('estado', 'RESERVADO')
            ->where('expira_en', '>', now())
            ->pluck('codigo')
            ->map(fn ($c) => trim((string) $c))
            ->all();

        return array_unique(array_merge($usados, $reservados));
    }
}
