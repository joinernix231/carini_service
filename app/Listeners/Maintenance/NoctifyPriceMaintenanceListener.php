<?php

namespace App\Listeners\Maintenance;

use App\Models\Maintenance\Maintenance;
use App\Notifications\PagoMantenimientoVerificado;
use App\Repositories\User\UserRepository;

class NoctifyPriceMaintenanceListener
{
    protected $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(NoctifyPriceMaintenanceListener $event): void
    {
        /**@var Maintenance $maintenance*/
        $maintenance = $event->maintenance;
        $client = $maintenance->client;

        $client->notify(new PagoMantenimientoVerificado($maintenance));

    }
}
