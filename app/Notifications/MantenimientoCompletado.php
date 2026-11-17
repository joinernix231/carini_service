<?php

namespace App\Notifications;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MantenimientoCompletado extends Notification implements ShouldQueue
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
        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice ? $firstDevice->client->name : 'Cliente';
        $technicianName = $this->maintenance->technician && $this->maintenance->technician->user
            ? $this->maintenance->technician->user->name
            : 'Técnico';

        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        return [
            'title' => "✅ Mantenimiento Finalizado",
            'body' => "El mantenimiento de {$deviceInfo} ha sido completado exitosamente por {$technicianName}",
            'data' => [
                'type' => 'maintenance_completed',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'technician_name' => $technicianName,
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
        $technicianName = $this->maintenance->technician && $this->maintenance->technician->user
            ? $this->maintenance->technician->user->name
            : 'Técnico';

        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        return [
            'title' => "✅ Mantenimiento Finalizado",
            'body' => "El mantenimiento de {$deviceInfo} ha sido completado exitosamente por {$technicianName}",
            'data' => [
                'type' => 'maintenance_completed',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'technician_name' => $technicianName,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}
