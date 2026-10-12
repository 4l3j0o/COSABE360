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
            'section_id' => $this->section_id,
            'user_id' => $this->user_id,
            'section' => $this->whenLoaded('section', fn () => [
                'id' => $this->section->id,
                'nombre' => $this->section->nombre,
            ]),
        ];
    }
}
