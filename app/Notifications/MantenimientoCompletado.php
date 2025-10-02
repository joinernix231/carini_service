<?php

namespace App\Notifications;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MantenimientoCompletado extends Notification implements ShouldQueue
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
        return [
            'title' => '✅ Mantenimiento Completado',
            'body' => "El mantenimiento creado el {$this->mantenimiento->created_at} ha sido completado exitosamente",
            'data' => [
                'type' => 'maintenance_completed',
                'maintenance_id' => $this->mantenimiento->id,
                'device_id' => $this->mantenimiento->device_id,
                'client_id' => $this->mantenimiento->client_id,
                'screen' => 'MantenimientoDetail',
                'priority' => 'normal'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }

}
