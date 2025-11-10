<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class MaintenanceInformationAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }
}

