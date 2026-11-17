<?php

namespace App\Broadcasting;

use App\Models\NotificationToken;
use App\Notifications\Channels\ExpoMessageAsWebPush;
use App\Services\ExpoPushService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * Canal 'expo': envía el mensaje de toExpo() a la app nativa (Expo/FCM) y
 * también, por Web Push, a los navegadores donde el usuario activó avisos en
 * la PWA. Uno no depende del otro: si falla uno, el otro igual sale.
 */
class ExpoChannel
{
    public function __construct(
        private readonly ExpoPushService $expoPushService
    ) {}

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        try {
            $message = $notification->toExpo($notifiable);
        } catch (\Exception $e) {
            Log::error('Error building push notification', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage()
            ]);
            return;
        }

        $this->sendExpo($notifiable, $message);
        $this->sendWebPush($notifiable, $message);
    }

    private function sendExpo($notifiable, array $message): void
    {
        try {
            // Obtener tokens activos del usuario
            $tokens = NotificationToken::where('user_id', $notifiable->id)
                ->active()
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                Log::info('No active Expo tokens found for user', ['user_id' => $notifiable->id]);
                return;
            }

            $result = $this->expoPushService->sendToTokens(
                $tokens,
                $message['title'],
                $message['body'],
                $message['data'] ?? []
            );

            Log::info('Expo notification sent', [
                'user_id' => $notifiable->id,
                'sent' => $result['sent'],
                'failed' => $result['failed']
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending Expo notification', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendWebPush($notifiable, array $message): void
    {
        if (!method_exists($notifiable, 'routeNotificationForWebPush')) {
            return;
        }

        if (empty(config('webpush.vapid.public_key')) || empty(config('webpush.vapid.private_key'))) {
            return; // Web Push sin configurar (faltan las llaves VAPID en .env)
        }

        try {
            if ($notifiable->pushSubscriptions()->doesntExist()) {
                return;
            }

            // El paquete borra solo las suscripciones vencidas (404/410).
            app(WebPushChannel::class)->send($notifiable, new ExpoMessageAsWebPush($message));

            Log::info('Web push notification sent', ['user_id' => $notifiable->id]);
        } catch (\Throwable $e) {
            Log::error('Error sending web push notification', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
