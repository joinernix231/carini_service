<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\UploadSupportPaymentMaintenance;
use App\Notifications\PagoMantenimientoSubido;
use App\Repositories\User\UserRepository;

class UploadSupportPaymentMaintenanceListener
{
    protected $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(UploadSupportPaymentMaintenance $event): void
    {
        $maintenance = $event->maintenance;
        $maintenance->load('clientDevices.client', 'clientDevices.device');

        $coordinators = $this->userRepository->findWhere([
            'role' => 'coordinador',
        ]);

        foreach ($coordinators as $coordinator) {
            if ($maintenance) {
                $coordinator->notify(new PagoMantenimientoSubido($maintenance));
            }
        }
    }
}
