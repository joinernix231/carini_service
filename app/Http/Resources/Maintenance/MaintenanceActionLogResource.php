<?php

namespace App\Http\Resources\Maintenance;

use Illuminate\Http\Request;
use App\Http\Resources\BaseJsonResource;

class MaintenanceActionLogResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'maintenance_id' => $this->resource->maintenance_id,
            'technician_id' => $this->resource->technician_id,
            'action' => $this->resource->action,
            'timestamp' => $this->resource->timestamp,
            'reason' => $this->resource->reason,
            'latitude' => $this->resource->latitude,
            'longitude' => $this->resource->longitude,
            'location' => $this->resource->location,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}

