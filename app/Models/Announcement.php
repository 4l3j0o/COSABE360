<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    public const ESTADOS = ['BORRADOR', 'PUBLICADO', 'ARCHIVADO'];
    public const TIPOS_DESTINATARIO = ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'];

    protected $fillable = [
        'titulo',
        'contenido',
        'author_id',
        'fecha_publicacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AnnouncementRecipient::class, 'announcement_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class, 'announcement_id');
    }

    public function isPublicado(): bool
    {
        return $this->estado === 'PUBLICADO';
    }

    public function scopePublicados($query)
    {
        return $query->where('estado', 'PUBLICADO')->orderByDesc('fecha_publicacion');
    }
}
