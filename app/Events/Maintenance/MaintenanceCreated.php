<?php

namespace App\Events\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceCreated
{
    use Dispatchable, SerializesModels;

    public Maintenance $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }
}
