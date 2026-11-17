<?php

namespace App\Traits;

use App\Repositories\User\UserRepository;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

trait NotifiesAdministrators
{
    /**
     * Notifica a todos los administradores con la notificación proporcionada
     *
     * @param Notification $notification La notificación a enviar
     * @param string $logMessage Mensaje para el log en caso de error
     * @param array $logContext Contexto adicional para el log
     * @return void
     */
    protected function notifyAdministrators(
        Notification $notification,
        string $logMessage,
        array $logContext = []
    ): void {
        $userRepository = $this->getUserRepository();

        // Obtener todos los administradores
        $administrators = $userRepository->findWhere([
            'role' => 'administrador',
        ]);

        foreach ($administrators as $administrator) {
            if ($administrator) {
                try {
                    $administrator->notify($notification);
                } catch (\Exception $e) {
                    Log::error($logMessage . ': ' . $e->getMessage(), array_merge([
                        'administrator_id' => $administrator->id,
                        'error' => $e->getTraceAsString(),
                    ], $logContext));
                }
            }
        }
    }

    abstract protected function getUserRepository(): UserRepository;
}

