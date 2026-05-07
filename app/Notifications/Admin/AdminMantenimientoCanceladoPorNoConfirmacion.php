<?php

namespace App\Notifications\Admin;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminMantenimientoCanceladoPorNoConfirmacion extends Notification implements ShouldQueue
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
            $this->maintenance->load('clientDevices.client', 'clientDevices.device', 'technician.user');
        }

        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
        $technicianName = $this->maintenance->technician && $this->maintenance->technician->user
            ? $this->maintenance->technician->user->name
            : 'Sin técnico asignado';
        $dateFormatted = $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('d/m/Y') : 'fecha programada';
        $value = $this->maintenance->value ? number_format($this->maintenance->value, 0, ',', '.') : '0';

        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "Mantenimiento #{$this->maintenance->id} de {$clientName} ({$deviceInfo}) programado para {$dateFormatted} fue cancelado por no confirmación. Técnico: {$technicianName}";

        return [
            'title' => '⚠️ Mantenimiento Cancelado - No Confirmación',
            'body' => $body,
            'data' => [
                'type' => 'admin_maintenance_cancelled_no_confirmation',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'device_count' => $deviceCount,
                'technician_name' => $technicianName,
                'value' => $this->maintenance->value,
                'date_maintenance' => $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('Y-m-d') : null,
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
            $this->maintenance->load('clientDevices.client', 'clientDevices.device', 'technician.user');
        }

        $deviceCount = $this->maintenance->clientDevices->count();
        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
        $technicianName = $this->maintenance->technician && $this->maintenance->technician->user
            ? $this->maintenance->technician->user->name
            : 'Sin técnico asignado';
        $dateFormatted = $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('d/m/Y') : 'fecha programada';

        $deviceInfo = $deviceCount === 1 && $firstDevice && $firstDevice->device
            ? "{$firstDevice->device->model}"
            : "{$deviceCount} equipos";

        $body = "Mantenimiento #{$this->maintenance->id} de {$clientName} ({$deviceInfo}) programado para {$dateFormatted} fue cancelado por no confirmación. Técnico: {$technicianName}";

        return [
            'title' => '⚠️ Mantenimiento Cancelado - No Confirmación',
            'body' => $body,
            'data' => [
                'type' => 'admin_maintenance_cancelled_no_confirmation',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'device_count' => $deviceCount,
                'technician_name' => $technicianName,
                'value' => $this->maintenance->value,
                'date_maintenance' => $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('Y-m-d') : null,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}

