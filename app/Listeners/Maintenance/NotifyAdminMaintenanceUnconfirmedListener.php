<?php

namespace App\Listeners\Maintenance;

use App\Traits\NotifiesAdministrators;
use App\Notifications\Admin\AdminMantenimientoSinConfirmar;
use App\Repositories\User\UserRepository;

class NotifyAdminMaintenanceUnconfirmedListener
{
    use NotifiesAdministrators;

    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function handle($maintenance): void
    {
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user'
        ]);

        $this->notifyAdministrators(
            new AdminMantenimientoSinConfirmar($maintenance),
            'Error enviando notificación de mantenimiento sin confirmar al administrador',
            ['maintenance_id' => $maintenance->id]
        );
    }
}

