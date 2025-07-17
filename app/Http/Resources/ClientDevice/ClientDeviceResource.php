<?php

namespace App\Http\Resources\ClientDevice;

use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Device\DeviceResource;
use App\Models\ClientDevice\ClientDevice;
use App\Utils\Resources\Resource;
use Illuminate\Http\Request;

/**
 * @property-read ClientDevice $resource
 */

class ClientDeviceResource extends Resource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'client_id' => $this->resource->client_id,
            'device_id' => $this->resource->device_id,
            'linked_by' => $this->resource->linked_by,
            'status' => $this->resource->status,
            'address' => $this->resource->address,
            'source' => $this->resource->source,
            'device' => new DeviceResource($this->whenLoaded('device')),
            'client' => $this->when(
                $this->resource->relationLoaded('client'),
                fn () => new ClientResource($this->resource->client)
            ),
            'created_at' => $this->resource->created_at?->toDateTimeString(),
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
