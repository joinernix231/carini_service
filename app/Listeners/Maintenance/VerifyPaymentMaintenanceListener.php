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

        $client->notify(new PagoMantenimientoVerificado($maintenance));

    }
}
