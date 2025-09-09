<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCreated;
use App\Jobs\Maintenance\SendCoordinatorMaintenance;

class NotifyCoordinatorMaintenanceListener
{

    public function handle(MaintenanceCreated $event): void
    {
        $maintenance = $event->maintenance;

        SendCoordinatorMaintenance::dispatch($maintenance->id)->onQueue('default');
    }
}
