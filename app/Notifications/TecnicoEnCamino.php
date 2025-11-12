<?php

namespace App\Notifications;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TecnicoEnCamino extends Notification implements ShouldQueue
{
    use Queueable;

    private Maintenance $mantenimiento;

    public function __construct(Maintenance $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function via($notifiable): array
    {
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->mantenimiento->relationLoaded('clientDevices')) {
            $this->mantenimiento->load('clientDevices.client', 'clientDevices.device', 'technician.user');
        }

        $deviceCount = $this->mantenimiento->clientDevices->count();
        $firstDevice = $this->mantenimiento->clientDevices->first();
        $technicianName = $this->mantenimiento->technician && $this->mantenimiento->technician->user
            ? $this->mantenimiento->technician->user->name
            : 'el técnico';

        $deviceInfo = $deviceCount === 1 && $firstDevice
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "{$technicianName} ya está en camino para realizar el mantenimiento de {$deviceInfo}";

        return [
            'title' => '🚗 Técnico en Camino',
            'body' => $body,
            'data' => [
                'type' => 'technician_on_the_way',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'technician_id' => $this->mantenimiento->technician_id,
                'device_count' => $deviceCount,
                'device_ids' => $this->mantenimiento->clientDevices->pluck('id')->toArray(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'high'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }

    public function toExpo($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->mantenimiento->relationLoaded('clientDevices')) {
            $this->mantenimiento->load('clientDevices.client', 'clientDevices.device', 'technician.user');
        }

        $deviceCount = $this->mantenimiento->clientDevices->count();
        $firstDevice = $this->mantenimiento->clientDevices->first();
        $technicianName = $this->mantenimiento->technician && $this->mantenimiento->technician->user
            ? $this->mantenimiento->technician->user->name
            : 'el técnico';

        $deviceInfo = $deviceCount === 1 && $firstDevice
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "{$technicianName} ya está en camino para realizar el mantenimiento de {$deviceInfo}";

        return [
            'title' => '🚗 Técnico en Camino',
            'body' => $body,
            'data' => [
                'type' => 'technician_on_the_way',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'technician_id' => $this->mantenimiento->technician_id,
                'device_count' => $deviceCount,
                'device_ids' => $this->mantenimiento->clientDevices->pluck('id')->toArray(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'high'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}

