<?php

namespace App\Http\Resources\Maintenance;

use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Technician\TechnicianResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestLocation = $this->getLatestLocation();
        $totalWorkTime = $this->getTotalWorkTime();
        $totalPausedTimeMs = $this->started_at ? $this->getTotalPausedTimeMs() : 0;

        return [
            // Información básica del mantenimiento
            'id' => $this->id,
            'type' => $this->type,
            'date_maintenance' => $this->date_maintenance,
            'shift' => $this->shift,
            'status' => $this->status,
            'value' => $this->value,
            'description' => $this->description,
            'observations' => $this->observations,
            'photo' => $this->photo,
            'signature_photo' => $this->signature_photo,
            'is_paid' => $this->is_paid,
            'payment_support' => $this->payment_support,
            'price_support' => $this->price_support,
            'spare_parts' => $this->spare_parts,
            'started_at' => $this->started_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'confirmation_required' => $this->confirmation_required ?? false,
            'confirmed_at' => $this->confirmed_at ? $this->confirmed_at->toISOString() : null,
            'confirmation_deadline' => $this->confirmation_deadline ? $this->confirmation_deadline->toISOString() : null,
            'coordinator_notified' => $this->coordinator_notified ?? false,
            'coordinator_notified_at' => $this->coordinator_notified_at ? $this->coordinator_notified_at->toISOString() : null,
            'coordinator_called' => $this->coordinator_called ?? false,
            'coordinator_called_at' => $this->coordinator_called_at ? $this->coordinator_called_at->toISOString() : null,

            // Información de tiempo
            'total_work_time' => $totalWorkTime,
            'total_pause_ms' => $totalPausedTimeMs,
            'total_pause_seconds' => round($totalPausedTimeMs / 1000, 2),
            'total_pause_formatted' => $this->formatPauseTime($totalPausedTimeMs),

            // Ubicación
            'latitude' => $latestLocation ? $latestLocation['latitude'] : null,
            'longitude' => $latestLocation ? $latestLocation['longitude'] : null,
            'location' => $latestLocation,

            // Cliente
            'client' => new ClientResource($this->whenLoaded('client')),

            // Técnico
            'technician' => new TechnicianResource($this->whenLoaded('technician')),

            // Dispositivos del cliente con información completa
            'devices' => $this->whenLoaded('clientDevices', function () {
                return $this->clientDevices->map(function ($clientDevice) {
                    $device = $clientDevice->device ?? null;
                    return [
                        'id' => $clientDevice->id,
                        'client_id' => $clientDevice->client_id,
                        'device' => [
                            'id' => $device ? $device->id : null,
                            'model' => $device ? $device->model : null,
                            'brand' => $device ? $device->brand : null,
                            'type' => $device ? $device->type : null,
                        ],
                        'serial' => $clientDevice->serial,
                        'address' => $clientDevice->address,
                        'pivot' => [
                            'description' => $clientDevice->pivot->description ?? null,
                            'progress_completed_indices' => $clientDevice->pivot->progress_completed_indices ?? null,
                            'progress_completed_count' => $clientDevice->pivot->progress_completed_count ?? null,
                            'progress_total' => $clientDevice->pivot->progress_total ?? null,
                            'progress_pct' => $clientDevice->pivot->progress_pct ?? null,
                            'progress_status' => $clientDevice->pivot->progress_status ?? null,
                            'progress_updated_at' => $clientDevice->pivot->progress_updated_at ?? null,
                        ],
                    ];
                });
            }),

            // Fotos del mantenimiento
            'photos' => $this->whenLoaded('photos', function () {
                return MaintenancePhotoResource::collection($this->photos);
            }),

            // Logs de acciones del mantenimiento
            'action_logs' => $this->whenLoaded('actionLogs', function () {
                return MaintenanceActionLogResource::collection($this->actionLogs->sortBy('timestamp'));
            }),

            // Sugerencias de repuestos
            'spare_part_suggestions' => $this->whenLoaded('sparePartSuggestions', function () {
                return SparePartSuggestionResource::collection($this->sparePartSuggestions);
            }),

            // Estadísticas resumidas
            'statistics' => [
                'photos_count' => $this->whenLoaded('photos', fn() => $this->photos->count(), 0),
                'action_logs_count' => $this->whenLoaded('actionLogs', fn() => $this->actionLogs->count(), 0),
                'spare_part_suggestions_count' => $this->whenLoaded('sparePartSuggestions', fn() => $this->sparePartSuggestions->count(), 0),
                'devices_count' => $this->whenLoaded('clientDevices', fn() => $this->clientDevices->count(), 0),
                'has_location' => $latestLocation !== null,
                'is_started' => $this->started_at !== null,
                'is_completed' => $this->status === 'completed',
                'is_paid' => $this->is_paid ?? false,
            ],
        ];
    }

    /**
     * Formatear el tiempo de pausa en formato legible
     */
    private function formatPauseTime(int $milliseconds): string
    {
        $seconds = round($milliseconds / 1000);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

