<?php

namespace App\Http\Resources\Coordinator;

use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
class CoordinatorResource extends BaseJsonResource
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
            'identification' => $this->resource->identification,
            'address' => $this->resource->address,
            'phone' => $this->resource->phone,
            'user_id' => $this->resource->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->resource->created_at?->toDateTimeString(),
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
