<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'name' => $this->resource->name,
          'email' => $this->resource->email,
          'role' => $this->resource->role,
        ];
    }
}
