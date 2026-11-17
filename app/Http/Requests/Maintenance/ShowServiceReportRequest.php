<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;

class ShowServiceReportRequest extends APIRequest
{
    use AuthorizesRoles;

    /**
     * Antes la ruta GET del reporte de servicio no tenía FormRequest: cualquier
     * usuario autenticado podía leer el reporte de cualquier mantenimiento.
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
