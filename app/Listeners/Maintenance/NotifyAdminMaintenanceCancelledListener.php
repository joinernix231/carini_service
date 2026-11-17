<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCancelled;
use App\Traits\NotifiesAdministrators;
use App\Notifications\Admin\AdminMantenimientoCanceladoPorNoConfirmacion;
use App\Repositories\User\UserRepository;

class NotifyAdminMaintenanceCancelledListener
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

    public function handle(MaintenanceCancelled $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user'
        ]);

        $this->notifyAdministrators(new AdminMantenimientoCanceladoPorNoConfirmacion($maintenance),
        'Error enviando notificación de mantenimiento cancelado al administrador',
            ['maintenance_id' => $maintenance->id]
        );
    }
}

