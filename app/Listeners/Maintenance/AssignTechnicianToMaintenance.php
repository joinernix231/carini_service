<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCreated;
use App\Jobs\Maintenance\AssignTechnicianJob;

class AssignTechnicianToMaintenance
{

    public function handle(MaintenanceCreated $event): void
    {
        $maintenance = $event->maintenance;

        AssignTechnicianJob::dispatch($maintenance->id)->onQueue('default');
    }
}
