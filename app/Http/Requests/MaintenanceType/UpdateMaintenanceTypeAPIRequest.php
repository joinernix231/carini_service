<?php

namespace App\Http\Requests\MaintenanceType;

use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceType;

class UpdateMaintenanceTypeAPIRequest extends APIRequest
{
    use AuthorizesRoles;


    public function authorize(): bool
    {
        return $this->userHasRole('administrador', 'coordinador');
    }

    public function rules(): array
    {
        return MaintenanceType::$rules;
    }
}
