<?php

namespace App\Notifications\Maintenance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceNotification extends Notification
{
    use Queueable;

    private $maintenance;

    public function __construct($maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $maintenance = $this->maintenance;
        $clientDevice = $maintenance->clientDevice;
        $client = $clientDevice->client;
        $device = $clientDevice->device;


        return (new MailMessage)
            ->subject('Nuevo Mantenimiento Por Asignar - ' . $client->name)
            ->view('mail.maintenance.assigned', [
                'maintenance' => $maintenance,
                'clientDevice' => $clientDevice,
                'client' => $client,
                'device' => $device,
                'coordinator' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
