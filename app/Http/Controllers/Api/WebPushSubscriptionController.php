<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\WebPushSubscriptionRequest;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Suscripciones Web Push de la PWA (navegador). La app nativa sigue usando
 * /notifications/register-token con Expo/FCM.
 */
class WebPushSubscriptionController extends Controller
{
    /**
     * Clave VAPID pública, por si la PWA se compiló sin EXPO_PUBLIC_VAPID_PUBLIC_KEY.
     */
    public function publicKey(): JsonResponse
    {
        $key = config('webpush.vapid.public_key');

        if (empty($key)) {
            return ResponseUtil::makeError('Web Push no está configurado en el servidor.', 503);
        }

        return ResponseUtil::makeResponse('Clave pública VAPID.', ['public_key' => $key]);
    }

    /**
     * Guarda (o actualiza) la suscripción del navegador para el usuario actual.
     * Si el mismo navegador estaba suscrito con otro usuario, pasa a este.
     */
    public function store(WebPushSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        $subscription = $user->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['content_encoding'] ?? 'aes128gcm'
        );

        return ResponseUtil::makeResponse('Suscripción guardada correctamente.', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Borra la suscripción del navegador (al cerrar sesión o desactivar avisos).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string', 'max:500']]);

        auth()->user()->deletePushSubscription($request->input('endpoint'));

        return ResponseUtil::makeResponse('Suscripción eliminada correctamente.');
    }
}
