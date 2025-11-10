<?php

namespace App\Notifications\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClienteConfirmoMantenimiento extends Notification implements ShouldQueue
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

        $firstDevice = $this->mantenimiento->clientDevices->first();
        $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';

        $body = "{$clientName} ha confirmado el mantenimiento #{$this->mantenimiento->id}. El técnico puede iniciar.";

        return [
            'title' => '✅ Cliente Confirmó Mantenimiento',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_confirmed',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'confirmed_at' => $this->mantenimiento->confirmed_at?->toISOString(),
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}

