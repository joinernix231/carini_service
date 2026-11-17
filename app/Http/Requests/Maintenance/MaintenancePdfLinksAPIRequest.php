<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;

class MaintenancePdfLinksAPIRequest extends APIRequest
{
    use AuthorizesRoles;

    /**
     * Admin/coordinador, el técnico asignado o el cliente dueño reciben los
     * enlaces firmados del PDF de la orden de servicio.
     */
    public function authorize(): bool
    {
        return $this->canAccessMaintenance($this->route('maintenance'));
    }

    public function rules(): array
    {
        return [];
    }
}
