<?php

namespace App\Events\Maintenance;

use App\Models\Maintenance\Maintenance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadSupportPaymentMaintenance
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Maintenance $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }
}
