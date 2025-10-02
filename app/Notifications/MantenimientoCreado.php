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
        return ['expo'];
    }

    public function toExpo($notifiable): array
    {
        return [
            'title' => '🔧 Nuevo Mantenimiento Creado',
            'body' => "Nuevo mantenimiento para {$this->maintenance->clientDevice->client->name} - {$this->maintenance->clientDevice->device->name}",
            'data' => [
                'type' => 'maintenance_created',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $this->maintenance->clientDevice->client_id,
                'device_id' => $this->maintenance->clientDevice->device_id,
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}
