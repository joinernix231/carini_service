<?php

namespace App\Notifications\Admin;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminPagoMantenimientoVerificado extends Notification implements ShouldQueue
{
    use Queueable;

    private Maintenance $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

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
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
        $value = $this->maintenance->value ? number_format($this->maintenance->value, 0, ',', '.') : '0';

        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "Pago de \${$value} verificado para mantenimiento #{$this->maintenance->id} de {$clientName} ({$deviceInfo})";

        return [
            'title' => '✅ Pago Verificado',
            'body' => $body,
            'data' => [
                'type' => 'admin_payment_verified',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'device_count' => $deviceCount,
                'value' => $this->maintenance->value,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
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
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
        $value = $this->maintenance->value ? number_format($this->maintenance->value, 0, ',', '.') : '0';

        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "Pago de \${$value} verificado para mantenimiento #{$this->maintenance->id} de {$clientName} ({$deviceInfo})";

        return [
            'title' => '✅ Pago Verificado',
            'body' => $body,
            'data' => [
                'type' => 'admin_payment_verified',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'device_count' => $deviceCount,
                'value' => $this->maintenance->value,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}

