<?php

namespace App\Http\Resources\Maintenance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenancePhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'maintenance_id' => $this->maintenance_id,
            'client_device_id' => $this->client_device_id,
            'photo_url' => $this->photo,
            'photo_type' => $this->photo_type,
            'uploaded_at' => $this->created_at,
        ];
    }
}
