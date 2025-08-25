<?php

namespace App\Http\Resources\Device;

use App\Models\Device\Device;
use App\Utils\Resources\Resource;
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
            'model' => $this->resource->model,
            'brand' => $this->resource->brand,
            'type' => $this->resource->type,
            'photo' => $this->resource->photo,
            'pdf_url' => $this->resource->pdf_url,
            'manufactured_at' => $this->resource->manufactured_at
        ];
    }
}
