<?php

namespace App\Notifications\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TecnicoAsignadoRequiereConfirmacion extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Maintenance $mantenimiento
    ) {}

    public function via($notifiable): array
    {
        return ['expo'];
    }

    public function toExpo($notifiable): array
    {
        // Asegurar que las relaciones estén cargadas
        if (!$this->mantenimiento->relationLoaded('clientDevices')) {
            $this->mantenimiento->load('clientDevices.client', 'clientDevices.device');
        }

        if (!$this->mantenimiento->relationLoaded('technician')) {
            $this->mantenimiento->load('technician.user');
        }

        $deviceCount = $this->mantenimiento->clientDevices->count();
        $firstDevice = $this->mantenimiento->clientDevices->first();
        $technicianName = $this->mantenimiento->technician && $this->mantenimiento->technician->user
            ? $this->mantenimiento->technician->user->name
            : 'el técnico';

        $shiftText = $this->mantenimiento->shift === 'AM' ? 'Mañana' : ($this->mantenimiento->shift === 'PM' ? 'Tarde' : $this->mantenimiento->shift);
        $dateFormatted = $this->mantenimiento->date_maintenance?->format('d/m/Y') ?? 'fecha pendiente';

        $body = "Se ha asignado el técnico {$technicianName} para el {$dateFormatted} ({$shiftText}). Por favor confirma tu mantenimiento.";

        return [
            'title' => '🔧 Técnico Asignado - Confirma tu Mantenimiento',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_assigned_requires_confirmation',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'device_count' => $deviceCount,
                'device_ids' => $this->mantenimiento->clientDevices->pluck('id')->toArray(),
                'date_maintenance' => $this->mantenimiento->date_maintenance?->format('Y-m-d'),
                'shift' => $this->mantenimiento->shift,
                'confirmation_deadline' => $this->mantenimiento->confirmation_deadline?->toISOString(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'high'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}

