<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'grado_id'       => $this->grado_id,
            'nombre'         => $this->nombre,
            'responsable_id' => $this->responsable_id,
            'capacidad'      => $this->capacidad,
            'estado'         => $this->estado,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'grade'          => $this->whenLoaded('grade', fn () => [
                'id'       => $this->grade->id,
                'nivel_id' => $this->grade->nivel_id,
                'nombre'   => $this->grade->nombre,
            ]),
            'responsable'    => $this->whenLoaded('responsable', fn () => [
                'id'   => $this->responsable->id,
                'nombre' => $this->responsable->nombre ?? null,
            ]),
        ];
    }
}
