<?php

namespace App\Notifications;

use App\Broadcasting\ExpoChannel;
use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoCreado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Maintenance $maintenance
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';

        $body = $deviceCount === 1
            ? "Mantenimiento para {$clientName} - {$firstDevice->device->model}"
            : "Mantenimiento para {$clientName} - {$deviceCount} equipos";

        return [
            'title' => 'Nuevo Mantenimiento Creado',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_created',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'device_ids' => $this->maintenance->clientDevices->pluck('id')->toArray(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }

    public function toExpo($notifiable): array
    {
        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';

        $body = $deviceCount === 1
            ? "Mantenimiento para {$clientName} - {$firstDevice->device->model}"
            : "Mantenimiento para {$clientName} - {$deviceCount} equipos";

        return [
            'title' => 'Nuevo Mantenimiento Creado',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_created',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'device_ids' => $this->maintenance->clientDevices->pluck('id')->toArray(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}
