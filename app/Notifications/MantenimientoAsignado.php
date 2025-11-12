<?php

namespace App\Notifications;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MantenimientoAsignado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Maintenance $mantenimiento
    ) {}

    public function via($notifiable): array
    {
        return ['expo'];
    }

    public function toExpo($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->mantenimiento->relationLoaded('clientDevices')) {
            $this->mantenimiento->load('clientDevices.client', 'clientDevices.device');
        }

        $deviceCount = $this->mantenimiento->clientDevices->count();
        $firstDevice = $this->mantenimiento->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';
        
        $deviceInfo = $deviceCount === 1 && $firstDevice
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "Se te ha asignado un mantenimiento para {$clientName} - {$deviceInfo}";

        return [
            'title' => '🔧 Mantenimiento Asignado',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_assigned',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'device_ids' => $this->mantenimiento->clientDevices->pluck('id')->toArray(),
                'date_maintenance' => $this->mantenimiento->date_maintenance?->format('Y-m-d'),
                'shift' => $this->mantenimiento->shift,
                'screen' => 'MantenimientoDetail',
                'priority' => 'high'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}
