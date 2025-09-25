<?php

namespace App\Http\Requests\Maintenance;



use App\Http\Requests\APIRequest;

class ReadMaintenanceAssignedAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador','administrador'];
        $user = session('user');

        return  in_array($user->role, $allowedRoles);
    }
}
