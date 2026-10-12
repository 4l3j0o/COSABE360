<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRecipient extends Model
{
    public $timestamps = false;

    public const TIPOS = ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'CURSO_ESPECIFICO', 'NIVEL_DOCENTES'];

    protected $fillable = ['announcement_id', 'tipo_destinatario', 'section_id', 'level_id', 'user_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
