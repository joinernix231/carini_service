<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'identifier' => $this->resource->identifier,
            'name' => $this->resource->name,
            'legal_representative' => $this->resource->legal_representative,
            'address' => $this->resource->address,
            'city' => $this->resource->city,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'client_type' => $this->resource->client_type,
            'user_id' => $this->resource->user_id,
            'created_at' => $this->resource->created_at?->toDateTimeString(),
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
