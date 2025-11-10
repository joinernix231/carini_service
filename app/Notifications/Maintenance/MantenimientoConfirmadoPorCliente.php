<?php

namespace App\Notifications\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MantenimientoConfirmadoPorCliente extends Notification implements ShouldQueue
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

        $shiftText = $this->mantenimiento->shift === 'AM' ? 'Mañana' : ($this->mantenimiento->shift === 'PM' ? 'Tarde' : $this->mantenimiento->shift);
        $dateFormatted = $this->mantenimiento->date_maintenance?->format('d/m/Y') ?? 'fecha pendiente';

        $body = "El cliente ha confirmado el mantenimiento programado para {$dateFormatted} ({$shiftText}). Ya puedes iniciar.";

        return [
            'title' => '✅ Mantenimiento Confirmado',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_confirmed_by_client',
                'maintenance_id' => $this->mantenimiento->id,
                'date_maintenance' => $this->mantenimiento->date_maintenance?->format('Y-m-d'),
                'shift' => $this->mantenimiento->shift,
                'confirmed_at' => $this->mantenimiento->confirmed_at?->toISOString(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'high'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }
}

