<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Models\ClientDevice\ClientDevice;

class ShowAdminMaintenanceAPIRequest extends APIRequest
{
    use AuthorizesRoles;


    public function authorize(): bool
    {
        return $this->userHasRole('coordinador', 'administrador');
    }
}
