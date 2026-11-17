<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCompleted;
use App\Traits\NotifiesAdministrators;
use App\Notifications\Admin\AdminMantenimientoCompletado;
use App\Repositories\User\UserRepository;

class NotifyAdminMaintenanceCompletedListener
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

    public function handle(MaintenanceCompleted $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user'
        ]);

        $this->notifyAdministrators(
            new AdminMantenimientoCompletado($maintenance),
            'Error enviando notificación de mantenimiento completado al administrador',
            ['maintenance_id' => $maintenance->id]
        );
    }
}

