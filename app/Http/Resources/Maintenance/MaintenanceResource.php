<?php

namespace App\Http\Resources\Maintenance;

use App\Http\Resources\Device\DeviceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
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
          'type' => $this->resource->type,
          'date_maintenance' => $this->resource->date_maintenance,
          'status' => $this->resource->status,
          'device' => new DeviceResource(optional($this->whenLoaded('clientDevice'))->device),
          'maintenance_type' => new MaintenanceTypeResource($this->whenLoaded('maintenanceType')),
          'description' => $this->resource->description,
          'photo' => $this->resource->photo,
        ];
    }
}
