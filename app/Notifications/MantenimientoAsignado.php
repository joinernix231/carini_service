<?php

namespace App\Notifications;

use App\Models\Maintenance\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoAsignado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Maintenance $mantenimiento
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'expo'];
    }

    public function toExpo($notifiable): array
    {
        return [
            'title' => '🔧 Mantenimiento Asignado',
            'body' => "Se te ha asignado un nuevo mantenimiento: {$this->mantenimiento->device->name}",
            'data' => [
                'type' => 'maintenance_assigned',
                'maintenance_id' => $this->mantenimiento->id,
                'device_id' => $this->mantenimiento->device_id,
                'client_id' => $this->mantenimiento->client_id,
                'screen' => 'MantenimientoDetail',
                'priority' => 'high'
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'high'
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔧 Nuevo Mantenimiento Asignado')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Se te ha asignado un nuevo mantenimiento.')
            ->line('**Equipo:** ' . $this->mantenimiento->device->name)
            ->line('**Cliente:** ' . $this->mantenimiento->client->name)
            ->line('**Tipo:** ' . $this->mantenimiento->type)
            ->line('**Prioridad:** ' . $this->mantenimiento->priority)
            ->action('Ver Detalles', url('/maintenance/' . $this->mantenimiento->id))
            ->line('Por favor revisa los detalles y confirma tu disponibilidad.')
            ->salutation('Saludos, Equipo Carini');
    }
}
