<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Models\ClientDevice\ClientDevice;

class ShowAdminMaintenanceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }
}
