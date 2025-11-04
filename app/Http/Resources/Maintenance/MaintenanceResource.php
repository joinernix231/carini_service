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
        $latestLocation = $this->getLatestLocation();

        return [
          'id' => $this->id,
          'type' => $this->type,
          'date_maintenance' => $this->date_maintenance,
          'shift' => $this->shift,
          'status' => $this->status,
          'value' => $this->value,
//          'spare_parts' => $this->spare_parts,
          'device' => $this->whenLoaded('clientDevices', function () {
              return $this->clientDevices->map(function ($clientDevice) {
                  return [
                      'id' => $clientDevice->device->id,
                      'model' => $clientDevice->device->model,
                      'client_device_id' => $clientDevice->id,
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
          'latitude' => $latestLocation ? $latestLocation['latitude'] : null,
          'longitude' => $latestLocation ? $latestLocation['longitude'] : null,
          'started_at' => $this->started_at,
          'total_pause_ms' => $this->started_at ? $this->getTotalPausedTimeMs() : 0,
          'description' => $this->description,
          'photo' => $this->photo,
          'is_paid' => $this->is_paid,
          'payment_support' => $this->payment_support,
          'price_support' => $this->price_support,
          'created_at' => $this->created_at,
          'spare_part_suggestions' => $this->whenLoaded('sparePartSuggestions', function () {
              return $this->sparePartSuggestions->map(function ($suggestion) {
                  $device = $suggestion->clientDevice->device ?? null;
                  return [
                      'id' => $suggestion->id,
                      'client_device_id' => $suggestion->client_device_id,
                      'device' => [
                          'id' => $suggestion->clientDevice->id,
                          'brand' => $device?->brand ?? null,
                          'model' => $device?->model ?? null,
                          'serial' => $suggestion->clientDevice->serial,
                      ],
                      'description' => $suggestion->description,
                      'photo' => $suggestion->photo,
                      'created_at' => $suggestion->created_at->toISOString(),
                      'updated_at' => $suggestion->updated_at->toISOString(),
                  ];
              });
          }),
        ];
    }
}
