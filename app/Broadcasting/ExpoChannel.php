<?php

namespace App\Broadcasting;

use App\Models\NotificationToken;
use App\Services\ExpoPushService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
            
            // Obtener tokens activos del usuario
            $tokens = NotificationToken::where('user_id', $notifiable->id)
                ->active()
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                Log::warning('No active tokens found for user', ['user_id' => $notifiable->id]);
                return;
            }

            // Enviar notificación
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
}
