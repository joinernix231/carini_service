<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;

class GetProgressRequest extends APIRequest
{
    use AuthorizesRoles;

    /**
     * Antes GET /technicianMaintenances/{maintenance}/progress no tenía FormRequest:
     * cualquier usuario autenticado podía leer el progreso de cualquier mantenimiento.
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
