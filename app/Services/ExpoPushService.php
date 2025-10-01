<?php

namespace App\Services;

use App\Models\NotificationToken;
use App\Models\User;
use App\Repositories\NotificationToken\NotificationTokenRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    private const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';
    private const MAX_TOKENS_PER_REQUEST = 100;

    public function __construct(
        private readonly Client $httpClient,
        private readonly NotificationTokenRepository $notificationTokenRepository
    ) {}

    /**
     * Enviar notificación a tokens específicos
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($tokens)) {
            return ['sent' => 0, 'failed' => 0];
        }

        $messages = $this->prepareMessages($tokens, $title, $body, $data);
        $chunks = array_chunk($messages, self::MAX_TOKENS_PER_REQUEST);

        $sent = 0;
        $failed = 0;

        foreach ($chunks as $chunk) {
            try {
                $result = $this->makeExpoRequest($chunk);
                $sent += $result['sent'];
                $failed += $result['failed'];
            } catch (\Exception $e) {
                Log::error('Error sending push notification', [
                    'error' => $e->getMessage(),
                    'tokens_count' => count($chunk)
                ]);
                $failed += count($chunk);
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Enviar notificación a usuarios específicos
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): array
    {
        $tokens = $this->getActiveTokensForUsers($userIds);
        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Enviar notificación a usuarios por rol
     */
    public function sendToRole(string $role, string $title, string $body, array $data = []): array
    {
        $userIds = User::where('role', $role)->pluck('id')->toArray();
        return $this->sendToUsers($userIds, $title, $body, $data);
    }

    /**
     * Obtener tokens activos para usuarios específicos
     */
    private function getActiveTokensForUsers(array $userIds): array
    {
        return $this->notificationTokenRepository->getActiveTokensForSending($userIds);
    }

    /**
     * Preparar mensajes para envío
     */
    private function prepareMessages(array $tokens, string $title, string $body, array $data = []): array
    {
        $messages = [];

        foreach ($tokens as $token) {
            $messages[] = [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'sound' => 'default',
                'badge' => 1,
                'priority' => 'high',
                'channelId' => 'default'
            ];
        }

        return $messages;
    }

    /**
     * Realizar petición a Expo API
     */
    private function makeExpoRequest(array $messages): array
    {
        try {
            $response = $this->httpClient->post(self::EXPO_API_URL, [
                'json' => $messages,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate'
                ],
                'timeout' => 30
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            $sent = 0;
            $failed = 0;

            if (isset($responseData['data'])) {
                foreach ($responseData['data'] as $receipt) {
                    if (isset($receipt['status']) && $receipt['status'] === 'ok') {
                        $sent++;
                    } else {
                        $failed++;
                        Log::warning('Push notification failed', [
                            'receipt' => $receipt
                        ]);
                    }
                }
            }

            Log::info('Push notifications sent', [
                'sent' => $sent,
                'failed' => $failed,
                'total' => count($messages)
            ]);

            return ['sent' => $sent, 'failed' => $failed];

        } catch (GuzzleException $e) {
            Log::error('Expo API request failed', [
                'error' => $e->getMessage(),
                'messages_count' => count($messages)
            ]);
            
            throw new \Exception('Error al enviar notificaciones: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si un token es válido
     */
    public function validateToken(string $token): bool
    {
        // Verificar formato básico de token Expo
        return preg_match('/^ExponentPushToken\[[a-zA-Z0-9_-]+\]$/', $token);
    }

    /**
     * Obtener estadísticas de tokens
     */
    public function getTokenStats(): array
    {
        return $this->notificationTokenRepository->getTokenStats();
    }

    /**
     * Limpiar tokens inactivos antiguos
     */
    public function cleanupInactiveTokens(int $daysOld = 30): int
    {
        return $this->notificationTokenRepository->cleanupInactiveTokens($daysOld);
    }
}
