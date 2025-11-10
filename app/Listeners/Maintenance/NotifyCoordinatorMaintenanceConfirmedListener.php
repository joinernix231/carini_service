<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceConfirmed;
use App\Notifications\Maintenance\ClienteConfirmoMantenimiento;
use App\Repositories\User\UserRepository;

class NotifyCoordinatorMaintenanceConfirmedListener
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(MaintenanceConfirmed $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user'
        ]);

        // Obtener todos los coordinadores
        $coordinators = $this->userRepository->findWhere([
            'role' => 'coordinador',
        ]);

        foreach ($coordinators as $coordinator) {
            if ($coordinator) {
                $coordinator->notify(new ClienteConfirmoMantenimiento($maintenance));
            }
        }
    }
}
