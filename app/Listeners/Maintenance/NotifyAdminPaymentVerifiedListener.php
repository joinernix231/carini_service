<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\VerifyPaymentMaintenance;
use App\Traits\NotifiesAdministrators;
use App\Notifications\Admin\AdminPagoMantenimientoVerificado;
use App\Repositories\User\UserRepository;

class NotifyAdminPaymentVerifiedListener
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

    public function handle(VerifyPaymentMaintenance $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device'
        ]);

        $this->notifyAdministrators(
            new AdminPagoMantenimientoVerificado($maintenance),
            'Error enviando notificación de pago verificado al administrador',
            ['maintenance_id' => $maintenance->id]
        );
    }
}

