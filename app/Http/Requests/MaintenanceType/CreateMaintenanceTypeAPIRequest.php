<?php

namespace App\Http\Requests\MaintenanceType;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceType;
use App\Rules\Maintenance\UniquePendingMaintenance;

class CreateMaintenanceTypeAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return MaintenanceType::$rules;
    }


}
