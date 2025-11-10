<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\VerifyPaymentMaintenance;
use App\Models\Maintenance\Maintenance;
use App\Notifications\PagoMantenimientoVerificado;
use App\Repositories\User\UserRepository;

class VerifyPaymentMaintenanceListener
{
    protected $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(VerifyPaymentMaintenance $event): void
    {
        /**@var Maintenance $maintenance*/
        $maintenance = $event->maintenance;
        $client = $maintenance->client;

        // Verificar que el cliente existe antes de enviar la notificación
        if ($client) {
            $client->notify(new PagoMantenimientoVerificado($maintenance));
        } else {
            // Log del error para debugging
            \Log::warning('No se pudo enviar notificación de pago verificado: cliente no encontrado para mantenimiento ID: ' . $maintenance->id);
        }
    }
}
