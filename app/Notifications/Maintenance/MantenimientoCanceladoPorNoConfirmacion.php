<?php

namespace App\Notifications\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MantenimientoCanceladoPorNoConfirmacion extends Notification implements ShouldQueue
{
    use Queueable;

    private Maintenance $mantenimiento;

    public function __construct(Maintenance $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function via($notifiable): array
    {
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->mantenimiento->relationLoaded('clientDevices')) {
            $this->mantenimiento->load('clientDevices.client', 'clientDevices.device');
        }

        $deviceCount = $this->mantenimiento->clientDevices->count();
        $firstDevice = $this->mantenimiento->clientDevices->first();
        $dateFormatted = $this->mantenimiento->date_maintenance?->format('d/m/Y') ?? 'fecha programada';

        $body = "Tu mantenimiento #{$this->mantenimiento->id} programado para el {$dateFormatted} ha sido cancelado porque no fue confirmado a tiempo.";

        return [
            'title' => '❌ Mantenimiento Cancelado',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_cancelled_no_confirmation',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'device_ids' => $this->mantenimiento->clientDevices->pluck('id')->toArray(),
                'date_maintenance' => $this->mantenimiento->date_maintenance?->format('Y-m-d'),
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }

    public function toExpo($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->mantenimiento->relationLoaded('clientDevices')) {
            $this->mantenimiento->load('clientDevices.client', 'clientDevices.device');
        }

        $deviceCount = $this->mantenimiento->clientDevices->count();
        $firstDevice = $this->mantenimiento->clientDevices->first();
        $dateFormatted = $this->mantenimiento->date_maintenance?->format('d/m/Y') ?? 'fecha programada';

        $body = "Tu mantenimiento #{$this->mantenimiento->id} programado para el {$dateFormatted} ha sido cancelado porque no fue confirmado a tiempo.";

        return [
            'title' => '❌ Mantenimiento Cancelado',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_cancelled_no_confirmation',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'device_ids' => $this->mantenimiento->clientDevices->pluck('id')->toArray(),
                'date_maintenance' => $this->mantenimiento->date_maintenance?->format('Y-m-d'),
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}

