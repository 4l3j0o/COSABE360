<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'contenido' => $this->contenido,
            'author_id' => $this->author_id,
            'fecha_publicacion' => $this->fecha_publicacion?->format('Y-m-d H:i:s'),
            'estado' => $this->estado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'nombre' => $this->author->nombre,
                'apellido' => $this->author->apellido,
                'email' => $this->author->email,
            ]),
            'recipients' => AnnouncementRecipientResource::collection($this->whenLoaded('recipients')),
        ];
    }
}
