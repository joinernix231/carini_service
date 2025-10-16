<?php

namespace App\Http\Resources\Maintenance;

use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\ClientDevice\ClientDeviceResource;
use App\Http\Resources\Device\DeviceResource;
use App\Http\Resources\Technician\TechnicianResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class MaintenanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $clientDevices = $this->whenLoaded('clientDevices');

        return [
          'id' => $this->resource->id,
          'type' => $this->resource->type,
          'date_maintenance' => $this->resource->date_maintenance,
          'shift' => $this->resource->shift,
          'status' => $this->resource->status,
          'value' => $this->resource->value,
//          'spare_parts' => $this->resource->spare_parts,
          'device' => $this->whenLoaded('clientDevices', function () {
              return $this->resource->clientDevices->map(function ($clientDevice) {
                  return [
                      'id' => $clientDevice->device->id,
                      'model' => $clientDevice->device->model,
                      'brand' => $clientDevice->device->brand,
                      'type' => $clientDevice->device->type,
                      'serial' => $clientDevice->serial,
                      'address' => $clientDevice->address,
                      'pivot_description' => $clientDevice->pivot->description ?? null,
                  ];
              });
          }),
          'client' => new ClientResource($this->whenLoaded('client')),
          'technician' => new TechnicianResource($this->whenLoaded('technician')),
          'description' => $this->resource->description,
          'photo' => $this->resource->photo,
          'is_paid' => $this->resource->is_paid,
          'payment_support' => $this->resource->payment_support,
          'price_support' => $this->resource->price_support,
          'created_at' => $this->resource->created_at,
        ];
    }
}
