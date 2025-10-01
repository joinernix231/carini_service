<?php

namespace App\Notifications;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AlertaUrgente extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Maintenance $mantenimiento,
        private readonly string $mensaje
    ) {}

    public function via($notifiable): array
    {
        return ['expo'];
    }

    public function toExpo($notifiable): array
    {
        return [
            'title' => '🚨 Alerta Urgente',
            'body' => $this->mensaje,
            'data' => [
                'type' => 'urgent_alert',
                'maintenance_id' => $this->mantenimiento->id,
                'device_id' => $this->mantenimiento->device_id,
                'client_id' => $this->mantenimiento->client_id,
                'screen' => 'MantenimientoDetail',
                'priority' => 'urgent'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }

}
