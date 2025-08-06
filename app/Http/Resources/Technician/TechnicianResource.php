<?php

namespace App\Http\Resources\Technician;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'document' => $this->resource->document,
            'phone' => $this->resource->phone,
            'address' => $this->resource->address,
            'status' => $this->resource->status,
        ];
    }
}
