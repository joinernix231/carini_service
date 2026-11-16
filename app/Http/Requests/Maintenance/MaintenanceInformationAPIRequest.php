<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Http\Requests\APIRequest;

class MaintenanceInformationAPIRequest extends APIRequest
{
    use AuthorizesRoles;

    public function authorize(): bool
    {
        if ($this->userHasRole('administrador', 'coordinador')) {
            return true;
        }

        $maintenance = $this->route('maintenance');
        if ($maintenance === null) {
            return false;
        }

        if ($this->userHasRole('tecnico')) {
            return session('technician_id') !== null
                && (int) $maintenance->technician_id === (int) session('technician_id');
        }

        if ($this->userHasRole('cliente')) {
            return session('client_id') !== null
                && $maintenance->clientDevices()->where('client_device.client_id', session('client_id'))->exists();
        }

        return false;
    }
}

