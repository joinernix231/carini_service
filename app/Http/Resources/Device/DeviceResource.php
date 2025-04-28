<?php

namespace App\Http\Resources\Device;

use App\Http\Resources\Client\ClientResource;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Device\Device;
use App\Utils\Resource;
use Illuminate\Http\Request;

/**
 * @property-read Device $resource
 */

class DeviceResource extends Resource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'serial' => $this->resource->serial,
            'model' => $this->resource->model,
            'brand' => $this->resource->brand,
            'type' => $this->resource->type,
            'manufactured_at' => $this->resource->manufactured_at
        ];
    }
}
