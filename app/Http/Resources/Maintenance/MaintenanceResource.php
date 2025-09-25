<?php

namespace App\Http\Resources\Maintenance;

use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Device\DeviceResource;
use App\Http\Resources\Technician\TechnicianResource;
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
          'shift' => $this->resource->shift,
          'status' => $this->resource->status,
          'value' => $this->resource->value,
          'spare_parts' => $this->resource->spare_parts,
          'device' => new DeviceResource(optional($this->whenLoaded('clientDevice'))->device),
          'client' => new ClientResource($this->whenLoaded('client')),
          'technician' => new TechnicianResource($this->whenLoaded('technician')),
          'description' => $this->resource->description,
          'photo' => $this->resource->photo,
          'is_paid' => $this->resource->is_paid,
          'created_at' => $this->resource->created_at,
        ];
    }
}
