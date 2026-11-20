<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Announcement extends Model
{
    public const ESTADOS = ['BORRADOR', 'PUBLICADO', 'ARCHIVADO'];
    public const TIPOS_DESTINATARIO = ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES', 'DOCENTES_CURSO'];

    protected $fillable = [
        'titulo',
        'contenido',
        'author_id',
        'fecha_publicacion',
        'estado',
        'destacado',
        'imagen_path',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'datetime',
            'destacado' => 'boolean',
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

    public function licencia(): HasOne
    {
        return $this->hasOne(Licencia::class, 'announcement_id');
    }

    /**
     * HU-82: los comunicados generados por una licencia son de solo lectura
     * en el módulo de comunicados (se gestionan desde Licencias).
     */
    public function esGeneradoPorLicencia(): bool
    {
        return $this->relationLoaded('licencia')
            ? $this->licencia !== null
            : $this->licencia()->exists();
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
