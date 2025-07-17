<?php

namespace App\Http\Requests\MaintenanceType;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceType;

class UpdateMaintenanceTypeAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return MaintenanceType::$rules;
    }
}
