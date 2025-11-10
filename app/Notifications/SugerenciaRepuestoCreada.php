<?php

namespace App\Notifications;

use App\Broadcasting\ExpoChannel;
use App\Models\Maintenance\SparePartSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SugerenciaRepuestoCreada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SparePartSuggestion $sparePartSuggestion
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        $suggestion = $this->sparePartSuggestion;
        $maintenance = $suggestion->maintenance;
        $clientDevice = $suggestion->clientDevice;
        $device = $clientDevice->device ?? null;
        $technician = $maintenance->technician->user ?? null;
        
        // Obtener nombre del técnico
        $technicianName = $technician ? $technician->name : 'El técnico';
        $deviceModel = $device ? $device->model : ($clientDevice->serial ?? 'tu equipo');
        $deviceBrand = $device ? $device->brand : '';

        $deviceInfo = $deviceBrand ? "{$deviceBrand} {$deviceModel}" : $deviceModel;
        $body = "{$technicianName} sugiere cambiar un repuesto en {$deviceInfo}";

        return [
            'title' => '🔧 Sugerencia de Repuesto',
            'body' => $body,
            'data' => [
                'type' => 'spare_part_suggestion_created',
                'suggestion_id' => $suggestion->id,
                'maintenance_id' => $maintenance->id,
                'client_device_id' => $clientDevice->id,
                'device_id' => $device?->id,
                'screen' => 'MaintenanceDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }

    public function toExpo($notifiable): array
    {
        $suggestion = $this->sparePartSuggestion;
        $maintenance = $suggestion->maintenance;
        $clientDevice = $suggestion->clientDevice;
        $device = $clientDevice->device ?? null;
        $technician = $maintenance->technician->user ?? null;
        
        // Obtener nombre del técnico
        $technicianName = $technician ? $technician->name : 'El técnico';
        $deviceModel = $device ? $device->model : ($clientDevice->serial ?? 'tu equipo');
        $deviceBrand = $device ? $device->brand : '';

        $deviceInfo = $deviceBrand ? "{$deviceBrand} {$deviceModel}" : $deviceModel;
        $body = "{$technicianName} sugiere cambiar un repuesto en {$deviceInfo}";

        return [
            'title' => '🔧 Sugerencia de Repuesto',
            'body' => $body,
            'data' => [
                'type' => 'spare_part_suggestion_created',
                'suggestion_id' => $suggestion->id,
                'maintenance_id' => $maintenance->id,
                'client_device_id' => $clientDevice->id,
                'device_id' => $device?->id,
                'screen' => 'MaintenanceDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}
