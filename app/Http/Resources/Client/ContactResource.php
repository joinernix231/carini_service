<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'client_id' => $this->resource->client_id,
            'nombre_contacto' => $this->resource->nombre_contacto,
            'correo' => $this->resource->correo,
            'telefono' => $this->resource->telefono,
            'direccion' => $this->resource->direccion,
            'cargo' => $this->resource->cargo,
            'created_at' => $this->resource->created_at?->toDateTimeString(),
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
