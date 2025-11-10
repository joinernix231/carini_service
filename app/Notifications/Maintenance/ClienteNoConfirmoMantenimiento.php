<?php

namespace App\Notifications\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClienteNoConfirmoMantenimiento extends Notification implements ShouldQueue
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
        $clientPhone = $firstDevice && $firstDevice->client ? $firstDevice->client->phone : null;
        $dateFormatted = $this->mantenimiento->date_maintenance?->format('d/m/Y') ?? 'fecha pendiente';

        $body = "{$clientName} no ha confirmado el mantenimiento #{$this->mantenimiento->id} programado para {$dateFormatted}. Por favor, llámalo.";

        return [
            'title' => '🚨 Cliente No Ha Confirmado Mantenimiento',
            'body' => $body,
            'data' => [
                'type' => 'maintenance_unconfirmed',
                'maintenance_id' => $this->mantenimiento->id,
                'client_id' => $firstDevice ? $firstDevice->client_id : null,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
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

