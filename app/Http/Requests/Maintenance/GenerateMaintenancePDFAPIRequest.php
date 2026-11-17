<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;

class GenerateMaintenancePDFAPIRequest extends APIRequest
{
    use AuthorizesRoles;

    public function authorize(): bool
    {
        if ($this->userHasRole('coordinador', 'administrador')) {
            return true;
        }

        // Un técnico solo genera el PDF de mantenimientos asignados a él.
        return $this->isAssignedTechnician($this->route('maintenance'));
    }

    public function rules(): array
    {
        return [];
    }
}

