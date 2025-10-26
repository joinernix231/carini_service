<?php

namespace App\Notifications;

use App\Broadcasting\ExpoChannel;
use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PagoMantenimientoSubido extends Notification implements ShouldQueue
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
        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';


        return [
            'title' => "Hola, {$clientName} tu pago del mantenimiento ha sido verificado",
            'body' => "Mantenimiento verificado",
            'data' => [
                'type' => 'maintenance_verify',
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
