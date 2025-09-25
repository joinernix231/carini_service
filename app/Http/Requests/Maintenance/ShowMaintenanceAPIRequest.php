<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;

class ShowMaintenanceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador','cliente'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }
}
