<?php

namespace App\Jobs\Maintenance;

use App\Mail\TSTMAIL;
use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Notifications\Maintenance\MaintenanceNotification;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendCoordinatorMaintenance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $maintenanceId;

    public function __construct(int $maintenanceId)
    {
        $this->maintenanceId = $maintenanceId;
    }

    public function handle(UserRepository $userRepository, MaintenanceRepository $maintenanceRepository): void
    {
        $maintenance = $maintenanceRepository->findWithoutFail($this->maintenanceId);
        $maintenance->load('clientDevice.client', 'clientDevice.device');

        $coordinators = $userRepository->findWhere([
            'role' => 'coordinador',
        ]);

        foreach ($coordinators as $coordinator)
        {
            if ($maintenance) {
                Notification::send($coordinator, new MaintenanceNotification($maintenance));
            }
        }




    }
}

