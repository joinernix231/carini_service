<?php

namespace App\Notifications\Admin;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminMantenimientoSinConfirmar extends Notification implements ShouldQueue
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

        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
        $clientPhone = $firstDevice && $firstDevice->client ? $firstDevice->client->phone : null;
        $technicianName = $this->maintenance->technician && $this->maintenance->technician->user
            ? $this->maintenance->technician->user->name
            : 'Sin técnico asignado';
        $dateFormatted = $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('d/m/Y') : 'fecha pendiente';
        $value = $this->maintenance->value ? number_format($this->maintenance->value, 0, ',', '.') : '0';

        $body = "⚠️ {$clientName} no ha confirmado el mantenimiento #{$this->maintenance->id} programado para {$dateFormatted}. Técnico: {$technicianName}";
        if ($this->maintenance->value) {
            $body .= ". Valor: \${$value}";
        }

        return [
            'title' => '🚨 Mantenimiento Sin Confirmar',
            'body' => $body,
            'data' => [
                'type' => 'admin_maintenance_unconfirmed',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'technician_name' => $technicianName,
                'date_maintenance' => $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('Y-m-d') : null,
                'shift' => $this->maintenance->shift,
                'confirmation_deadline' => $this->maintenance->confirmation_deadline ? $this->maintenance->confirmation_deadline->toISOString() : null,
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
            $this->maintenance->load('clientDevices.client', 'clientDevices.device', 'technician.user');
        }

        $firstDevice = $this->maintenance->clientDevices->first();
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
        $clientPhone = $firstDevice && $firstDevice->client ? $firstDevice->client->phone : null;
        $technicianName = $this->maintenance->technician && $this->maintenance->technician->user
            ? $this->maintenance->technician->user->name
            : 'Sin técnico asignado';
        $dateFormatted = $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('d/m/Y') : 'fecha pendiente';
        $value = $this->maintenance->value ? number_format($this->maintenance->value, 0, ',', '.') : '0';

        $body = "⚠️ {$clientName} no ha confirmado el mantenimiento #{$this->maintenance->id} programado para {$dateFormatted}. Técnico: {$technicianName}";
        if ($this->maintenance->value) {
            $body .= ". Valor: \${$value}";
        }

        return [
            'title' => '🚨 Mantenimiento Sin Confirmar',
            'body' => $body,
            'data' => [
                'type' => 'admin_maintenance_unconfirmed',
                'maintenance_id' => $this->maintenance->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'technician_name' => $technicianName,
                'date_maintenance' => $this->maintenance->date_maintenance ? $this->maintenance->date_maintenance->format('Y-m-d') : null,
                'shift' => $this->maintenance->shift,
                'confirmation_deadline' => $this->maintenance->confirmation_deadline ? $this->maintenance->confirmation_deadline->toISOString() : null,
                'value' => $this->maintenance->value,
                'screen' => 'MantenimientoDetail',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}

