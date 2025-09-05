<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'identifier' => $this->resource->identifier,
            'name' => $this->resource->name,
            'legal_representative' => $this->resource->legal_representative,
            'address' => $this->resource->address,
            'city' => $this->resource->city,
            'department' => $this->resource->department,
            'phone' => $this->resource->phone,
            'client_type' => $this->resource->client_type,
            'document_type' => $this->resource->document_type,
            'user_id' => $this->resource->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'created_at' => $this->resource->created_at?->toDateTimeString(),
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
