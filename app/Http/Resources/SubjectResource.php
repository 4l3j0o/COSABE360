<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nombre'          => $this->nombre,
            'descripcion'     => $this->descripcion,
            'level_id'        => $this->level_id,
            'horas_semanales' => $this->horas_semanales,
            'estado'          => $this->estado,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'level'           => $this->whenLoaded('level', fn () => [
                'id'     => $this->level->id,
                'nombre' => $this->level->nombre,
                'orden'  => $this->level->orden,
            ]),
        ];
    }
}
