<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCompleted;
use App\Jobs\GenerateMaintenancePDFJob;

class GenerateMaintenancePDFListener
{
    public function handle(MaintenanceCompleted $event): void
    {
        GenerateMaintenancePDFJob::dispatch($event->maintenance->id)
            ->onQueue(config('maintenance_pdf.queue'));
    }
}

