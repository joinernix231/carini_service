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

        // Determinar si requiere pago o no
        $requiresPayment = !is_null($this->maintenance->is_paid);
        
        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        if ($requiresPayment) {
            $body = "Mantenimiento cotizado para {$deviceInfo}. Requiere pago.";
        } else {
            $body = "Mantenimiento cotizado para {$deviceInfo}. No requiere pago";
        }

        return [
            'title' => "Hola, {$clientName} Mantenimiento Cotizado",
            'body' => $body,
            'data' => [
                'type' => 'maintenance_quoted',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'requires_payment' => $requiresPayment,
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

        // Determinar si requiere pago o no
        $requiresPayment = !is_null($this->maintenance->is_paid);
        
        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        if ($requiresPayment) {
            $body = "Mantenimiento cotizado para {$deviceInfo}. Requiere pago";
        } else {
            $body = "Mantenimiento cotizado para {$deviceInfo}. No requiere pago";
        }

        return [
            'title' => "Hola, {$clientName} Mantenimiento Cotizado",
            'body' => $body,
            'data' => [
                'type' => 'maintenance_quoted',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'requires_payment' => $requiresPayment,
                'value' => $this->maintenance->value,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}
