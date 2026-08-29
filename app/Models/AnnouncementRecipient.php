<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRecipient extends Model
{
    public const TIPOS = ['TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO'];

    protected $fillable = [
        'announcement_id',
        'tipo_destinatario',
        'course_id',
        'user_id',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
