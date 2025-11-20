<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCompleted;
use App\Jobs\GenerateMaintenancePDFJob;

class GenerateMaintenancePDFListener
{
    public function handle(MaintenanceCompleted $event): void
    {
        $maintenance = $event->maintenance;

        $maintenance->load([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
        ]);

        // Encolar el job en la queue 'PDFQueue_local'
        GenerateMaintenancePDFJob::dispatch($maintenance)
            ->onQueue('PDFQueue_local');
    }
}

