<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementRecipientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'announcement_id' => $this->announcement_id,
            'tipo_destinatario' => $this->tipo_destinatario,
            'course_id' => $this->course_id,
            'user_id' => $this->user_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'nombre' => $this->course->nombre,
            ]),
        ];
    }
}
