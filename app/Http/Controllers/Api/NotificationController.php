<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\RegisterTokenRequest;
use App\Http\Requests\Api\SendNotificationRequest;
use App\Http\Resources\Notification\NotificationResource;
use App\Repositories\NotificationToken\NotificationTokenRepository;
use App\Services\ExpoPushService;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly ExpoPushService $expoPushService,
        private readonly NotificationTokenRepository $notificationTokenRepository
    ) {}

    /**
     * Registrar token de notificación
     */
    public function registerToken(RegisterTokenRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;

        $token = $this->notificationTokenRepository->registerOrUpdateToken($data);

        return ResponseUtil::makeResponse('Token registrado correctamente.', [
            'token_id' => $token->id,
            'platform' => $token->platform,
            'device_model' => $token->device_model
        ]);
    }

    /**
     * Desregistrar token de notificación
     */
    public function unregisterToken(Request $request): JsonResponse
    {
        $user = auth()->user();
        $token = $request->input('token');

        if ($token) {
            $this->notificationTokenRepository->deactivateToken($user->id, $token);
        } else {
            $this->notificationTokenRepository->deactivateAllUserTokens($user->id);
        }

        return ResponseUtil::makeResponse('Token desregistrado correctamente.');
    }

    /**
     * Enviar notificación
     */
    public function sendNotification(SendNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $this->expoPushService->sendToUsers(
                $data['userIds'],
                $data['title'],
                $data['body'],
                $data['data'] ?? []
            );

            return ResponseUtil::makeResponse('Notificaciones enviadas correctamente.', [
                'sent' => $result['sent'],
                'failed' => $result['failed'],
                'total' => count($data['userIds'])
            ]);

        } catch (\Exception $e) {
            return ResponseUtil::makeError('Error al enviar notificaciones: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener notificaciones del usuario
     */
    public function getUserNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $perPage = $request->input('per_page', 20);
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseUtil::makeResponse('Notificaciones obtenidas correctamente.', [
            'notifications' => NotificationResource::collection($notifications->items()),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'last_page' => $notifications->lastPage(),
            ]
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return ResponseUtil::makeError('Notificación no encontrada.', 404);
        }

        $notification->markAsRead();

        return ResponseUtil::makeResponse('Notificación marcada como leída.');
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $user->unreadNotifications->markAsRead();

        return ResponseUtil::makeResponse('Todas las notificaciones marcadas como leídas.');
    }

    /**
     * Obtener tokens del usuario actual
     */
    public function getUserTokens(Request $request): JsonResponse
    {
        $user = auth()->user();

        $tokens = $this->notificationTokenRepository->getUserTokens($user->id);

        return ResponseUtil::makeResponse('Tokens obtenidos correctamente.', [
            'tokens' => $tokens
        ]);
    }

    /**
     * Enviar notificación por rol
     */
    public function sendToRole(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|in:cliente,tecnico,coordinador,admin',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'sometimes|array'
        ]);

        try {
            $result = $this->expoPushService->sendToRole(
                $request->role,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            return ResponseUtil::makeResponse('Notificaciones enviadas por rol correctamente.', [
                'sent' => $result['sent'],
                'failed' => $result['failed'],
                'role' => $request->role
            ]);

        } catch (\Exception $e) {
            return ResponseUtil::makeError('Error al enviar notificaciones por rol: ' . $e->getMessage(), 500);
        }
    }
}
