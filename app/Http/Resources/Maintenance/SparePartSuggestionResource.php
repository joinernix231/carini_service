<?php

namespace App\Http\Resources\Maintenance;

use App\Http\Resources\ClientDevice\ClientDeviceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SparePartSuggestionResource extends JsonResource
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
            'device' => $this->whenLoaded('clientDevice', function () {
                $device = $this->clientDevice->device ?? null;
                return [
                    'id' => $this->clientDevice->id,
                    'brand' => $device?->brand ?? null,
                    'model' => $device?->model ?? null,
                    'serial' => $this->clientDevice->serial,
                ];
            }),
            'description' => $this->description,
            'photo' => $this->photo,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
