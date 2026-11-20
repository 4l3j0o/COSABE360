<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationHistory extends Model
{
    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'modelo',
        'modelo_id',
        'detalle',
        'ip_address',
        'user_agent',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            abort(403, 'El historial de auditoría no es editable');
        });
        static::deleting(function () {
            abort(403, 'El historial de auditoría no es eliminable');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
