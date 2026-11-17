<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;

class ShowMaintenanceAPIRequest extends APIRequest
{
    use AuthorizesRoles;

    public function authorize(): bool
    {
        if ($this->userHasRole('coordinador', 'administrador')) {
            return true;
        }

        // Un cliente solo ve mantenimientos de sus propios equipos.
        $maintenance = $this->route('maintenance');

        return $this->userHasRole('cliente')
            && $maintenance !== null
            && session('client_id') !== null
            && $maintenance->clientDevices()->where('client_device.client_id', session('client_id'))->exists();
    }
}
