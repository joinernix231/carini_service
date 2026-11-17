<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Adapta el mensaje que ya arma cada notificación para Expo (toExpo) a un
 * mensaje Web Push. Así las ~20 notificaciones existentes llegan también a
 * la PWA sin tener que escribir un toWebPush() en cada una.
 */
class ExpoMessageAsWebPush extends Notification
{
    /**
     * @param array{title: string, body: string, data?: array} $expoMessage
     */
    public function __construct(private readonly array $expoMessage) {}

    public function toWebPush($notifiable, $notification = null): WebPushMessage
    {
        $data = $this->expoMessage['data'] ?? [];
        $type = $data['type'] ?? 'general';
        $tag = isset($data['maintenance_id']) ? "{$type}-{$data['maintenance_id']}" : $type;

        return (new WebPushMessage())
            ->title($this->expoMessage['title'] ?? 'Carini')
            ->body($this->expoMessage['body'] ?? '')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-192.png')
            ->tag($tag)
            ->data($data)
            ->options([
                'TTL' => 60 * 60 * 24, // si el teléfono está apagado, se entrega hasta 24 h después
                'urgency' => ($data['priority'] ?? null) === 'high' ? 'high' : 'normal',
            ]);
    }
}
