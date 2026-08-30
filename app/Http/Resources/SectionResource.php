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
            'grade_id'       => $this->grade_id,
            'nombre'         => $this->nombre,
            'teacher_id' => $this->teacher_id,
            'capacidad'      => $this->capacidad,
            'estado'         => $this->estado,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'grade'          => $this->whenLoaded('grade', fn () => [
                'id'       => $this->grade->id,
                'level_id' => $this->grade->level_id,
                'nombre'   => $this->grade->nombre,
            ]),
            'responsable'    => $this->whenLoaded('responsable', fn () => [
                'id'       => $this->responsable->id,
                'nombres'  => $this->responsable->nombres,
                'apellidos'=> $this->responsable->apellidos,
            ]),
        ];
    }
}
