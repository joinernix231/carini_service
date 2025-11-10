<?php

namespace App\Notifications;

use App\Broadcasting\ExpoChannel;
use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoCotizado extends Notification implements ShouldQueue
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
            ? "Mantenimiento cotizado para {$firstDevice->device->model}"
            : "Mantenimiento cotizado para {$deviceCount} equipos";

        return [
            'title' => "Hola, {$clientName} Mantenimiento Cotizado",
            'body' => $body,
            'data' => [
                'type' => 'maintenance_quoted',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'screen' => 'MantenimientoDetail',
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
            ? "Mantenimiento cotizado para {$firstDevice->device->model}"
            : "Mantenimiento cotizado para {$deviceCount} equipos";

        return [
            'title' => "Hola, {$clientName} Mantenimiento Cotizado",
            'body' => $body,
            'data' => [
                'type' => 'maintenance_quoted',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}
