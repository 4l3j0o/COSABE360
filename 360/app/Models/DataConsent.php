<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DataConsent extends Model
{
    protected $fillable = [
        'titular_type',
        'titular_id',
        'finalidad',
        'consentimiento',
        'fecha_consentimiento',
        'fecha_revocacion',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'consentimiento' => 'boolean',
            'fecha_consentimiento' => 'date',
            'fecha_revocacion' => 'date',
        ];
    }

    public function titular(): MorphTo
    {
        return $this->morphTo();
    }

    public function activo(): bool
    {
        return $this->consentimiento && $this->fecha_revocacion === null;
    }
}
