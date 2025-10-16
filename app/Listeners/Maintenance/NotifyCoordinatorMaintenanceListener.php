<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCreated;
use App\Jobs\Maintenance\SendCoordinatorMaintenance;
use App\Notifications\MantenimientoCreado;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\User\UserRepository;

class NotifyCoordinatorMaintenanceListener
{
    protected $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(MaintenanceCreated $event): void
    {
        $maintenance = $event->maintenance;
        $maintenance->load('clientDevices.client', 'clientDevices.device');

        $coordinators = $this->userRepository->findWhere([
            'role' => 'coordinador',
        ]);

        foreach ($coordinators as $coordinator)
        {
            if ($maintenance) {
                $coordinator->notify(new MantenimientoCreado($maintenance));
            }
        }
    }
}
