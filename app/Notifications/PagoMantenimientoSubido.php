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
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->maintenance->relationLoaded('clientDevices')) {
            $this->maintenance->load('clientDevices.client', 'clientDevices.device');
        }

        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';
        
        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "El cliente {$clientName} ha subido el soporte de pago para el mantenimiento - {$deviceInfo}";

        return [
            'title' => '💳 Soporte de Pago Subido',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_payment_uploaded',
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
        // Asegurar que las relaciones estén cargadas
        if (!$this->maintenance->relationLoaded('clientDevices')) {
            $this->maintenance->load('clientDevices.client', 'clientDevices.device');
        }

        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';
        
        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "El cliente {$clientName} ha subido el soporte de pago para el mantenimiento - {$deviceInfo}";

        return [
            'title' => '💳 Soporte de Pago Subido',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_payment_uploaded',
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
