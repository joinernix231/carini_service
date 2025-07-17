<?php

namespace App\Http\Requests\MaintenanceType;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;

class ShowMaintenanceTypeAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
